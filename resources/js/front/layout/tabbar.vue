<template>
    <div>

        <router-view :key="key">
        </router-view>

        <mt-tabbar v-model="selected">
            <mt-tab-item id="首页">
                <router-link to="/dashboard" slot="icon">
                    <svg-icon iconClass="Home"></svg-icon>
                </router-link>
                首页
            </mt-tab-item>
            <mt-tab-item id="新建活动">
                <router-link to="/activity/create" slot="icon">
                    <svg-icon iconClass="create"></svg-icon>
                </router-link>
                新建活动
            </mt-tab-item>
        </mt-tabbar>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex'
    export default {
        data(){
            return {
                selected:'首页'
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
