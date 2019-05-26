<template>
    <div>
        <mt-header :title="name">
            <router-link to="/example" slot="left">
                <mt-button icon="back">返回</mt-button>
            </router-link>
            <router-link to="/dashboard" slot="right">
                <svg-icon iconClass="Home"></svg-icon>
            </router-link>

        </mt-header>
        <router-view :key="key">
        </router-view>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex'
    export default {
        data(){
            return {

            }
        },
        computed:{

            ...mapGetters([
                'name'
            ]),

            key(){
                return this.$route.fullPath;
            }
        }
    }
</script>
