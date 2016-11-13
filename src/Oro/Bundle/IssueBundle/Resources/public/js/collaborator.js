define(function(require) {
    'use strict';

    var _ = require('underscore');
    var Collaborator;
    var mediator = require('oroui/js/mediator');
    var BaseComponent = require('oroui/js/app/components/base/component');

    Collaborator = BaseComponent.extend({

        /**
         * @property {Object}
         */
        options: {
            gridName: 'collaborators-issue'
        },

        /**
         * @inheritDoc
         */
        initialize: function(options) {
            this.options = _.defaults(options || {}, this.options);

            mediator.on('widget_success:note-dialog', this._refresh, this);
        },

        /**
         * refresh datagrid
         */
        _refresh: function() {
            mediator.trigger('datagrid:doRefresh:' + this.options.gridName);
        }
    });

    return Collaborator;
});
