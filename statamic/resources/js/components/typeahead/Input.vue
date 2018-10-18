<template>
    <input type="text"
        autocomplete="off"
        v-model="query"
        @keydown.up="up"
        @keydown.down="down"
        @keydown.enter="hit"
        @keydown.esc="reset"
        @blur="reset"
        placeholder="{{ translate('cp.search') }}..."
    />
</template>


<script>
export default {

    props: ['query', 'onUp', 'onDown', 'onHit', 'onReset'],

    methods: {

        up: function() {
            this.onUp();
        },

        down: function() {
            this.onDown();
        },

        hit: function() {
            this.onHit();
        },

        reset: function() {
            this.onReset();
        }
    }

};
</script>
