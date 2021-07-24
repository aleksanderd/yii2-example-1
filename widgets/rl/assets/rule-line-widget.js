
(function( $ ) {

    var methods = {

        sortable2Value: function() {
            var value = [];
            this.data('$sortable').find('li').each(function(k, v) {
                value.push($(this).attr('item-id'));
            });
            this.data('value', value);
            this.RuleLineWidget('updateInputs');
            console.log(value);
            return this;
        },

        updateInputs: function() {
            var $widget = this, options = this.data('options'), value = this.data('value');
            $widget.find('input[type="hidden"]').remove();
            if (value.length > 0) {
                $.each(value, function(index, id){
                    $widget.append('<input type="hidden" name="' + options.name + '[' + index +']" value="' + id + '">');
                });
            } else {
                $widget.append('<input type="hidden" name="' + options.name + '" value="empty">');
            }
            return this;
        },

        addItem: function(id) {
            id = parseInt(id, 10);
            var $widget = this, value = $widget.data('value'), id2item = $widget.data('id2item');
            if (value.indexOf(id) > -1) {
                console.log('rl.addItem: item already in the list, id = ' + id);
                return this;
            }
            if (id2item[id] === undefined) {
                console.log('rl.addItem: undefined item, id = ' + id);
                return this;
            }
            value.push(id);
            $widget.data('value', value);
            $widget.RuleLineWidget('updateSelect');
            $widget.RuleLineWidget('updateSortable');
            $widget.RuleLineWidget('updateInputs');
            return this;
        },

        removeItem: function(id) {
            id = parseInt(id, 10);
            var $widget = this, value = $widget.data('value'), id2item = $widget.data('id2item');
            if (id2item[id] === undefined) {
                console.log('rl.removeItem: undefined item, id = ' + id);
                return this;
            }
            for (var i in value) {
                if (value[i] == id) {
                    value.splice(i, 1);
                }
            }
            $widget.data('value', value);
            $widget.RuleLineWidget('updateSelect');
            $widget.RuleLineWidget('updateSortable');
            $widget.RuleLineWidget('updateInputs');
            return this;
        },

        updateSelect: function() {
            var $widget = this, items = $widget.data('items'), value = $widget.data('value');
            var options = $widget.data('options'), content = '';
            $.each(items, function(index, item) {
                if (value.indexOf(item.id) < 0) {
                    content += '<option value="' + item.id + '">' + item.name + '</option>';
                }
            });
            $widget.data('$select').html(content).val("").change();
        },

        updateSortable: function() {
            var $widget = this, id2item = $widget.data('id2item'),
                value = $widget.data('value'), $emptyHint = $widget.data('$emptyHint');
            var options = $widget.data('options'), content = '';
            if (value.length > 0) {
                $emptyHint.hide();
            } else {
                $emptyHint.show();
            }
            $.each(value, function(index, id) {
                if (id2item[id] === undefined) {
                    return;
                }
                content += '<li role="option" draggable="true" item-id="' + id + '"><div>' +
                    '<span class="pull-right"><button class="btn btn-xs btn-danger" type="button">' +
                    options.itemRemoveBtnLabel + '</button></span><span>' +
                    options.sortableItemLabel(id2item[id]) + '</span></div></li>';
            });
            var $sortable = $widget.data('$sortable');
            $sortable.html(content).find('li button').on('click', function() {
                var id = $(this).closest('li').attr('item-id');
                $widget.RuleLineWidget('removeItem', id);
            });
            $sortable.sortable().bind('sortupdate', function(e, ui) {
                $widget.RuleLineWidget('sortable2Value');
            });
        },

        setItems: function(items) {
            var id2item = [];
            for (var i in items) {
                id2item[items[i].id] = items[i];
            }
            this.data('items', items);
            this.data('id2item', id2item);
            return this;
        },

        setValue: function(value) {
            var $widget = this;
            if (value === undefined || value === null) {
                value = [];
            }
            for (var i in value) {
                value[i] = parseInt(value[i], 10);
            }
            $widget.data('value', value);
            return this;
        },

        itemsByUserId: function(user_id) {
            var $widget = this, options =this.data('options');
            $.ajax({
                data: { user_id: user_id },
                url: options.url,
                type: 'POST'
            }).done(function(data, status, xhr) {
                $widget.RuleLineWidget('setItems', data.output);
                $widget.RuleLineWidget('setValue', []);
                $widget.RuleLineWidget('updateInputs');
                $widget.RuleLineWidget('updateSelect');
                $widget.RuleLineWidget('updateSortable');
            }).fail(function(xhr, status, thrown) {
                alert('Error: Getting lines list failed :(');
            });
            return this;
        },

        init: function(params) {
            var $widget = this;
            var options = $.extend({
                url: '/client-line/select-list',
                itemRemoveBtnLabel: 'Remove',
                sortableItemLabel: function(item) {
                    return '<b>' + item.name + '</b><br><small>' + item.info + '</small>';
                }
            }, params);
            $widget.data('options', options);
            $widget.RuleLineWidget('setItems', options.items);
            $widget.RuleLineWidget('setValue', options.value);

            var $select = $('#' + this.attr('id') + '-select');
            var $addBtn = $('#' + this.attr('id') + '-add-btn');
            var $sortable = $('#' + this.attr('id') + '-sortable');
            var $emptyHint = $('#' + this.attr('id') + '-empty');
            $widget.data('$select', $select);
            $widget.data('$addBtn', $addBtn);
            $widget.data('$sortable', $sortable);
            $widget.data('$emptyHint', $emptyHint);

            $addBtn.on('click', function() {
                var val = $select.val(); //$select.select2('val');
                console.log(val);
                if (val > 0) {
                    $widget.RuleLineWidget('addItem', val);
                } else {
                    $select.select2('open');
                }
            });
            $widget.RuleLineWidget('updateSortable');
            $widget.RuleLineWidget('updateSelect');

            if (options.user_input_id !== undefined) {
                $('#' + options.user_input_id).on('change', function() {
                    var user_id = $(this).val();
                    $widget.RuleLineWidget('itemsByUserId', user_id);
                });
            }

            return this;
        }

    };

    $.fn.RuleLineWidget = function(method) {

        if ( methods[method] ) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || ! method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('No such ' +  method + ' in jQuery.RuleLineWidget');
        }

    };

})(jQuery);