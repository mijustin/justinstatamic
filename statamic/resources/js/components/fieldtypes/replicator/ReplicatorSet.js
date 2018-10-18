export default {

    props: ['data', 'index', 'config', 'parentName', 'sets'],

    data() {
        return {
            collapsedPreview: null
        }
    },

    computed: {

        display() {
            return this.config.display || this.data.type;
        },

        instructions() {
            return this.config.instructions;
        },

        hasMultipleFields() {
            return this.config.fields.length > 1;
        },

        isHidden() {
            return this.data['#hidden'] === true;
        }

    },

    ready() {
        this.collapsedPreview = this.getCollapsedPreview();
    },

    watch: {

        data: {
            deep: true,
            handler() {
                this.collapsedPreview = this.getCollapsedPreview();
            }
        }

    },

    methods: {

        delete() {
            swal({
                type: 'warning',
                title: translate('cp.are_you_sure'),
                confirmButtonText: translate('cp.yes_im_sure'),
                cancelButtonText: translate('cp.cancel'),
                showCancelButton: true
            }, () => {
                this.$emit('deleted', this.index);
            });
        },

        toggle() {
            (this.isHidden) ? this.expand() : this.collapse();
        },

        expand(all) {
            Vue.set(this.data, '#hidden', false);

            // The 'all' variable will be true if it was called from the expandAll() method.
            this.$emit('expanded', this, all);
        },

        collapse() {
            Vue.set(this.data, '#hidden', true);
        },

        getCollapsedPreview() {
            return _.map(this.$children, (fieldtype) => {
                if (fieldtype.config.replicator_preview === false) return;

                return (typeof fieldtype.getReplicatorPreviewText !== 'undefined')
                    ? fieldtype.getReplicatorPreviewText()
                    : JSON.stringify(fieldtype.data);
            }).filter(t => t !== null && t !== '' && t !== undefined).join(' / ');
        },

        focus() {
            // We want to focus the first field.
            const field = this.$children[0];

            // If the component doesn't know how to focus, we cannot.
            if (typeof field.focus !== 'function') return;

            field.focus();
        },

        fieldClasses: function (field) {
            return [
                `form-group p-2 m-0 ${field.type}-fieldtype`,
                tailwind_width_class(field.width)
            ];
        },

        componentName(type) {
            return type.replace('.', '-') + '-fieldtype';
        }

    }

}
