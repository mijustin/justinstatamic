<template>
    <div>
        <div id="typeahead" v-cloak :class="{'dirty': isDirty}">
            <div class="state-container">
                <i class="icon icon-magnifying-glass" @click="focus"></i>
            </div>

            <typeahead-input class="search alt"
                            id="global-search"
                            :query.sync="query"
                            :on-up="up"
                            :on-down="down"
                            :on-hit="hit"
                            :on-reset="reset"
                            @keyup.esc="reset"
            ></typeahead-input>

            <i class="icon icon-cross" v-show="isDirty || loading" @click="reset"></i>

            <ul v-show="hasItems">
                <li v-for="item in items" :class="{'active': isActive($index)}" @mousedown="hit" @mousemove="setActive($index)">
                    <span class="title" v-html="item.title"></span>
                    <span class="url" v-html="item.url"></span>
                </li>
            </ul>
        </div>
    </div>
</template>


<script>
export default {

    props: {
        limit: Number,
        src: String
    },

    data: function () {
        return {
            items: [],
            query: '',
            current: -1,
            loading: false
        }
    },

    components: {
        'typeahead-input': require('./Input.vue')
    },

    computed: {
        hasItems: function () {
            return this.items.length > 0;
        },

        isEmpty: function () {
            return !this.query && !this.loading;
        },

        isDirty: function () {
            return !!this.query && !this.loading;
        }
    },

    methods: {
        update: function () {
            if (!this.query) {
                this.reset();
                return;
            }

            this.loading = true;

            this.$http.get(this.src, Object.assign({q:this.query}, this.data)).success(function (data) {
                if (this.query) {
                    this.items = !!this.limit ? data.slice(0, this.limit) : data;
                    this.current = -1;
                    this.loading = false;
                }
            }.bind(this));
        },

        reset: function () {
            this.items = [];
            this.query = '';
            this.loading = false;
        },

        setActive: function (index) {
            this.current = index;
        },

        isActive: function (index) {
            return this.current == index;
        },

        focus: function() {
            $('#global-search').focus();
        },

        hit: function () {
            if (this.hasItems) {
                window.location.href = this.items[this.current].edit_url;
            }
        },

        up: function () {
            if (this.current > 0) this.current--;
        },

        down: function () {
            if (this.current < this.items.length-1) this.current++;
        }
    },

    ready: function() {
        this.$watch('query', function(newval, oldval) {
            this.update();
        });
    }
};
</script>
