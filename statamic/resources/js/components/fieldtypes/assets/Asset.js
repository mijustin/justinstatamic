import Luminous from 'luminous-lightbox';

export default {

    components: {
        AssetEditor: require('../../assets/Editor/Editor.vue')
    },

    props: {
        asset: Object
    },

    data() {
        return {
            editing: false
        }
    },


    computed: {

        isImage() {
            return this.asset.is_image;
        },

        canShowSvg() {
            return this.asset.extension === 'svg' && ! this.asset.url.includes(':');
        },

        thumbnail() {
            return this.asset.thumbnail;
        },

        toenail() {
            return this.asset.toenail;
        },

        label() {
            return this.asset.title || this.asset.basename;
        }
    },


    methods: {

        edit() {
            this.editing = true;
        },

        remove() {
            this.$emit('removed', this.asset);
        },

        makeZoomable() {
            const el = $(this.$el).find('a.zoom')[0];

            if (! el || ! this.isImage) return;

            new Luminous(el, {
                closeOnScroll: true,
                captionAttribute: 'title'
            });
        },

        closeEditor() {
            this.editing = false;
        },

        assetSaved(asset) {
            this.asset = asset;
            this.closeEditor();
        }

    },


    ready() {
        this.makeZoomable();
    }

}
