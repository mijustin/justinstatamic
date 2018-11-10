<template>

    <div class="bard-link-toolbar" :style="{
        position: 'absolute',
        top: positionTop,
        left: positionLeft
    }">
        <div class="flex items-center px-2">
            <a
                :href="actualLink"
                v-text="actualLink"
                class="link"
                target="_blank"
                v-show="!isEditing"
            ></a>
            <input
                v-el:input
                v-show="isEditing"
                v-model="link"
                class="input"
                @keydown.enter.prevent="commit"
            />
            <div class="bard-link-toolbar-buttons">
                <button @click="edit" v-show="!isEditing" v-tip :tip-text="translate('cp.edit_link')">
                    <i class="fa fa-pencil"></i>
                </button>
                <button @click="remove" v-show="hasLink && !isEditing" v-tip :tip-text="translate('cp.remove_link')">
                    <i class="fa fa-unlink"></i>
                </button>
                <button @click="commit" v-show="isEditing" v-tip :tip-text="translate('cp.done')">
                    <i class="fa fa-check"></i>
                </button>
            </div>
        </div>
        <div class="p-sm pt-1 border-t border-faint-white" v-show="isEditing">
            <label class="text-xxs text-white flex items-center">
                <input class="checkbox mr-1" type="checkbox" v-model="targetBlank">
                {{ translate('cp.open_in_new_window') }}
            </label>
        </div>
    </div>

</template>

<script>
export default {

    props: {
        config: Object, // The bard config
    },

    data() {
        return {
            link: null, // the link being typed
            actualLink: null, // the link on the element
            targetBlank: false, // the state of the checkbox
            actuaTargetBlank: null, // the target on the element
            positionTop: '-999em',
            positionLeft: '-999em',
            isEditing: false,
            anchorElement: null, // The <a> tag

            // The following items are available on the instance because they are set
            // directly from the scribe plugin, but they don't need to be reactive.
            //
            // scribe,  // The scribe instance
            // comman, // The linkTooltipCommand instance
            // createCallback, // The function to be called after creating a link.
        }
    },

    computed: {

        hasLink() {
            return this.actualLink != null;
        },

        sanitizedLink() {
            const str = this.link;

            return str.match(/^\w[\w\-_\.]+\.(co|uk|com|org|net|gov|biz|info|us|eu|de|fr|it|es|pl|nz)/i) ?
                        'https://' + str :
                            str;
        }

    },

    watch: {

        // When the scribe plugin focused a different anchor, this will get updated.
        anchorElement(el) {
            if (el) this.updateStateFromAnchor(el);
        }

    },

    methods: {

        edit() {
            this.isEditing = true;
            this.$nextTick(() => this.$els.input.select());
        },

        remove() {
            this.selectAnchorContent();
            new this.scribe.api.Command('unlink').execute();
            getSelection().collapseToEnd();
        },

        commit() {
            (this.anchorElement) ? this.update() : this.create();
        },

        update() {
            this.scribe.transactionManager.run(() => {
                this.anchorElement.href = this.sanitizedLink;
                this.applyAttributes(this.anchorElement);
            });

            this.isEditing = false;

            this.updateStateFromAnchor(this.anchorElement);
        },

        create() {
            const el = this.createCallback.call(null, this.sanitizedLink);
            this.applyAttributes(el);
            this.anchorElement = el;
        },

        updateStateFromAnchor(el) {
            this.actualLink = this.link = el.getAttribute('href');
            this.actualTargetBlank = this.targetBlank = el.getAttribute('target') === '_blank';
        },

        applyAttributes(el) {
            if (this.targetBlank) {
                el.target = '_blank';
            } else {
                el.removeAttribute('target');
            }

            let rels = [];
            if (this.config.link_noopener) rels.push('noopener');
            if (this.config.link_noreferrer) rels.push('noreferrer');

            if (rels.length) {
                el.rel = rels.join(' ');
            } else {
                el.removeAttribute('rel');
            }
        },

        resetState() {
            this.link = null;
            this.actualLink = null;
            this.targetBlank = this.config.target_blank || false;
            this.actualTargetBlank = this.targetBlank;
            this.anchorElement = null;
        },

        // Extends selection to whole anchor. Returns anchor node or undefined.
        selectAnchorContent() {
            const selection = new this.scribe.api.Selection;
            var node, range;

            // nothing selected?
            if (typeof selection.range === 'undefined' || selection.range.collapsed) {
                node = selection.getContaining(function (testNode) {
                    return testNode.nodeName === 'A';
                });

                // are we inside an <a>?
                if (node) {
                    range = document.createRange();
                    range.selectNode(node);
                    selection.selection.removeAllRanges();
                    selection.selection.addRange(range);
                }
            }

            return node;
        },

    }

}
</script>
