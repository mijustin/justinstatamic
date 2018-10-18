import VueToast from '../components/toast/main.js';

export default {
    components: { VueToast },

    data: {
        toast: null,
        flash: Statamic.flash,
    },

    methods: {
        flashExistingMessages() {
            this.flash.forEach(
                ({ type, message }) => this.setFlashMessage(message, { theme: type })
            );
        },

        bindToastNotifications() {
            this.toast = this.$refs.toast;
            if (this.toast) {
                this.toast.setOptions({
                    position: 'bottom right',
                });
            }
        },

        setFlashMessage(message, opts) {
            this.toast.showToast(message, {
                theme:    opts.theme,
                timeLife: opts.timeout || 5000,
                closeBtn: opts.hasOwnProperty('dismissible') ? opts.dismissible : true,
            });
        },
    },

    events: {
        setFlashSuccess(message, opts) {
            opts = opts || {};
            opts.theme = 'success';
            this.setFlashMessage(message, opts);
        },

        setFlashError(message, opts) {
            opts = opts || {};
            opts.theme = 'danger';
            this.setFlashMessage(message, opts);
        },
    },

    ready() {
        this.bindToastNotifications();
        this.flashExistingMessages();
    },
}
