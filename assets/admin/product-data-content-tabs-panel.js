(function($, w){

    var itemNumber = 0,
        prefix = 'nemishkor-wc-custom-tabs',
        rootClassName = '_nemishkor-wc-custom-tabs-root',
        itemsClassName = 'nemishkor-wc-custom-tabs-items',
        itemClassName = 'nemishkor-wc-custom-tabs-item',
        itemDeleteClassName = 'nemishkor-wc-custom-tabs-item-delete',
        itemIndexClassName = 'nemishkor-wc-custom-tabs-item-index',
        itemTitleLabelClassName = 'nemishkor-wc-custom-tabs-item-title-label',
        itemTitleValueClassName = 'nemishkor-wc-custom-tabs-item-title-value',
        itemContentLabelClassName = 'nemishkor-wc-custom-tabs-item-content-label',
        itemContentValueClassName = 'nemishkor-wc-custom-tabs-item-content-value',
        $root,
        $items;

    $(document).on('ready', function(){
        $root = $('.' + rootClassName);
        init();
    });

    function init() {

        $items = $('<div>').addClass(itemsClassName);
        $root.append($items);

        for( var tabIndex in w.nemishkorWCCustomTabs ){
            if( w.nemishkorWCCustomTabs.hasOwnProperty(tabIndex) ) {
                appendItem(w.nemishkorWCCustomTabs[tabIndex]);
            }
        }

        $root.append(
            $('<button>')
                .addClass('button')
                .text('Add tab')
                .on('click', function(e){
                    e.preventDefault();
                    appendItem(null);
                })
        );

    }

    function appendItem(tab) {

        itemNumber++;

        var contentTextAreaID = prefix + '-tab-content-' + itemNumber;
        var $item = renderItem(tab, contentTextAreaID);

        $items.append($item);

        if( tab === null ){
            $item.slideDown(200);
        }

        wp.editor.initialize( contentTextAreaID, { tinymce: true } );

    }

    function renderItem(tab, contentTextAreaID) {

        return $('<div>')
            .addClass(itemClassName)
            .css( 'display', tab ? 'block' : 'none' )
            .append(
                $('<input>')
                    .attr('type', 'hidden')
                    .attr('name', prefix + '[' + itemNumber + '][index]')
                    .val(tab ? tab.index : '')
            )
            .append(
                $('<span>')
                    .addClass(itemIndexClassName)
                    .text(tab ? '#' + tab.index : '')
            )
            .append(
                $('<button>')
                    .addClass('button ' + itemDeleteClassName)
                    .text('Delete tab')
                    .on( 'click', function(e){
                        e.preventDefault();
                        $(this)
                            .parents('.' + itemClassName)
                            .first()
                            .slideUp( 400, function(){ $(this).remove() } )
                    } )
            )
            .append(
                $('<div>')
                    .append(
                        $('<label>')
                            .addClass(itemTitleLabelClassName)
                            .attr( 'for', prefix + '-tab-title-' + itemNumber )
                            .text('Tab title')
                    )
            )
            .append(
                $('<div>')
                    .append(
                        $('<input>')
                            .addClass(itemTitleValueClassName)
                            .attr('name', prefix + '[' + itemNumber + '][title]')
                            .attr('type', 'text')
                            .attr( 'id', prefix + '-tab-title-' + itemNumber )
                            .val(tab ? tab.title : '')
                    )
            )
            .append(
                $('<div>')
                    .append(
                        $('<label>')
                            .addClass(itemContentLabelClassName)
                            .attr( 'for', prefix + '-tab-content-' + itemNumber )
                            .text('Tab content')
                    )
            )
            .append(
                $('<div>')
                    .append(
                        $('<textarea>')
                            .addClass(itemContentValueClassName)
                            .attr('name', prefix + '[' + itemNumber + '][content]')
                            .attr( 'id', contentTextAreaID )
                            .val(tab ? tab.content : '')
                    )
            );

    }

})(jQuery, window);