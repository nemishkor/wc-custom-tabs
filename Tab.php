<?php
/**
 * Created by PhpStorm.
 * User: nemishkor
 * Date: 25.03.19
 * Time: 21:16
 */

namespace Nemishkor\WCCustomTabs;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Tab implements \JsonSerializable {

    private $index;
    private $title;
    private $content;

    /**
     * Tab constructor.
     * @param int|null $index
     * @param string $title
     * @param string $content
     */
    public function __construct($index = null, string $title = '', string $content = '') {
        $this->index = $index;
        $this->title = $title;
        $this->content = $content;
    }

    /**
     * @return int|null
     */
    public function getIndex() {
        return $this->index;
    }

    /**
     * @param int|null $index
     */
    public function setIndex($index) {
        $this->index = $index;
    }

    /**
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title) {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getContent() {
        return $this->content;
    }

    /**
     * @param string $content
     */
    public function setContent($content) {
        $this->content = $content;
    }

    public function jsonSerialize() {
        return [
            'index' => $this->getIndex(),
            'title' => $this->getTitle(),
            'content' => $this->getContent()
        ];
    }

}