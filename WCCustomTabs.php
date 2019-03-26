<?php
/**
 * Created by PhpStorm.
 * User: nemishkor
 * Date: 25.03.19
 * Time: 20:45
 */

namespace Nemishkor;

if (!defined( 'ABSPATH' )) {
    exit;
}

use Nemishkor\WCCustomTabs\Tab;

class NemishkorWCCustomTabs {

    private $baseDir;
    private $baseUrl;
    private $textDomain;
    private $productPanelName = 'custom_content_tabs';
    private $metaKey = 'nemishkor-wc-custom-tab';

    public function __construct($baseDir, $textDomain) {
        $this->baseDir = plugin_dir_path( $baseDir );
        $this->baseUrl = plugin_dir_url( $baseDir );
        $this->textDomain = $textDomain;
        $this->addActions();
        $this->addFilters();
    }

    private function addFilters() {

        add_filter( 'pll_copy_post_metas', [ $this, 'pll_copy_post_metas' ], 10, 2 );
        add_filter( 'woocommerce_product_tabs', [ $this, 'woocommerce_product_tabs' ] );

    }

    private function addActions() {

        add_action( 'init', [ $this, 'loadTextDomain' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueueAdminScripts' ] );
        add_action( 'woocommerce_product_data_tabs', [ $this, 'addProductDataTab' ] );
        add_action( 'woocommerce_product_data_panels', [ $this, 'outputProductDataPanel' ] );
        add_action( 'save_post', [ $this, 'updateProductTermOnSavePost' ], 10, 2 );

    }

    public function loadTextDomain() {
        load_plugin_textdomain(
            $this->textDomain,
            false,
            basename( $this->baseDir ) . '/languages'
        );
    }

    public function enqueueAdminScripts() {

        $screen = get_current_screen();

        if ($screen->id !== 'product') {
            return;
        }

        wp_enqueue_editor();

        wp_register_style(
            'nemishkor-wc-custom-tab-css',
            $this->baseUrl . '/assets/admin/product-data-content-tabs-panel.css',
            false,
            '1.0.0'
        );

        wp_enqueue_style( 'nemishkor-wc-custom-tab-css' );

        wp_register_script(
            'nemishkor-wc-custom-tab-js',
            $this->baseUrl . '/assets/admin/product-data-content-tabs-panel.js',
            'jquery',
            '1.0.0'
        );

        global $post;

        $tabs = get_post_meta( $post->ID, 'nemishkor-wc-custom-tab' );

        wp_localize_script(
            'nemishkor-wc-custom-tab-js',
            'nemishkorWCCustomTabs',
            [ 'l10n_print_after' => 'nemishkorWCCustomTabs = ' . json_encode( $tabs ) . ';' ]
        );

        wp_enqueue_script( 'nemishkor-wc-custom-tab-js' );

    }

    public function addProductDataTab($tabs) {

        $tabs['content_tabs'] = array(
            'label' => __( 'Custom content tabs', $this->textDomain ),
            'target' => $this->productPanelName,
            'class' => [],
            'priority' => 80,
        );

        return $tabs;

    }

    /**
     * @throws \Exception
     */
    public function outputProductDataPanel() {

        $this->renderView(
            $this->baseDir . '/views/admin/product-data-content-tabs-panel',
            [
                'productPanelName' => $this->productPanelName,
                'textDomain' => $this->textDomain,
            ]
        );

    }

    public function updateProductTermOnSavePost($post_id, \WP_Post $post) {

        if ($post->post_type !== 'product') {
            return;
        }

        /**
         * @var Tab[] $oldTabs
         */

        $oldTabs = get_post_meta( $post_id, $this->metaKey );
        $newTabs = [];
        $maxIndex = 0;

        if (!empty( $_POST['nemishkor-wc-custom-tabs'] )) {
            foreach ($_POST['nemishkor-wc-custom-tabs'] as $rawTab) {
                $tab = new Tab( isset( $rawTab['index'] ) ? intval( $rawTab['index'] ) : null, $rawTab['title'], $rawTab['content'] );
                if ($tab->getIndex()) {
                    $maxIndex = max( $maxIndex, $tab->getIndex() );
                }
                $newTabs[] = $tab;
            }
        }

        foreach ($newTabs as $newTab) {

            if (!$newTab->getIndex()) {
                $maxIndex++;
                $newTab->setIndex( $maxIndex );
                add_post_meta( $post_id, $this->metaKey, $newTab );
                continue;
            }

            foreach ($oldTabs as $oldTabKey => $oldTab) {
                if ($newTab->getIndex() === $oldTab->getIndex()) {
                    update_post_meta( $post_id, $this->metaKey, $newTab, $oldTab );
                    unset( $oldTabs[ $oldTabKey ] );
                }
            }

        }

        foreach ($oldTabs as $oldTab) {
            delete_post_meta( $post_id, $this->metaKey, $oldTab );
        }

    }

    /**
     * Allows plugins to copy custom fields when a new post (or page) translation is created or synchronizing them. Filter arguments:
     * @param $metas - an array of post metas
     * @param $sync - false when copying custom fields to a new translation, true when synchronizing translations
     */
    public function pll_copy_post_metas($metas, $sync) {

        if ($sync && in_array( $this->metaKey, $metas )) {
            unset( $metas[ $this->metaKey ] );
        }

    }

    public function woocommerce_product_tabs($woocommerceTabs) {

        global $post;

        /**
         * @var Tab[]|null $postCustomTabs
         */

        $postCustomTabs = get_post_meta( $post->ID, 'nemishkor-wc-custom-tab' );

        if (!$postCustomTabs) {
            return $woocommerceTabs;
        }

        foreach ($postCustomTabs as $postCustomTab) {
            $woocommerceTabs[ $this->metaKey . '-' . $postCustomTab->getIndex() ] = [
                'title' => $postCustomTab->getTitle(),
                'priority' => 24,
                'callback' => [ $this, 'woocommerceProductTabCallback' ],
                'WCCustomTab' => $postCustomTab
            ];
        }

        return $woocommerceTabs;

    }

    /**
     * @param string $key
     * @param array $tab
     */
    public function woocommerceProductTabCallback($key, $tab) {

        echo $tab['WCCustomTab']->getContent();

    }

    /**
     * @param    string  view filename
     * @param array $view_data
     * @return void
     * @throws \Exception
     */
    private function renderView($file = NULL, $view_data = []) {

        if (!preg_match( '/\.php$/', $file )) {
            $file .= '.php';
        }

        extract( $view_data, EXTR_SKIP );
        ob_start();

        try {
            /** @noinspection PhpIncludeInspection */
            include $file;
        } catch (\Exception $e) {
            ob_end_clean();
            throw $e;
        }

        echo ob_get_clean();

    }

}