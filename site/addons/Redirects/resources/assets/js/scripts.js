Vue.component('redirects-list', {
    mixins: [Dossier],
    props: ['translations', 'get', 'delete', 'reorder', 'columns', 'actions'],
    data: function () {
        return {
            ajax: {
                get: this.get,
                delete: this.delete,
                reorder: this.reorder
            },
            tableOptions: {
                checkboxes: true,
                partials: {
                    cell: `
                        <span v-if="column.value === 'from' || column.value === 'to'" class="cell-slug">{{{ formatValue(item[column.value]) }}}</span>
                        <template v-else>{{{ formatValue(item[column.value]) }}}</template>
                        `
                }
            },
        };
    },
    created: function () {
        this.addActionPartials();
    },
    methods: {
        addActionPartials: function () {
            let actions = '';

            if (this.actions.indexOf('edit') > -1) {
                actions += `<li><a :href="item.edit_url">{{ translate('cp.edit') }}</a></li>`;
            }

            if (this.actions.indexOf('add_manual_redirect') > -1) {
                actions += `<li><a :href="item.create_redirect_url + '?from=' + item.url">${this.t('manual_redirect_create')}</a></li>`;
            }

            if (this.actions.indexOf('delete') > -1) {
                actions += `<li class="warning"><a href="#" @click.prevent="call('deleteItem', item.id)">{{ translate('cp.delete') }}</a></li>`;
            }

            this.tableOptions.partials.actions = actions;
        },
        t: function (key) {
            return this.translations[key] || key;
        }
    }
});
