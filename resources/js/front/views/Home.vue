<template>
    <div>
        <router-view ></router-view>
    </div>
</template>

<script>
    export default {
        data(){
            return {

            }
        },
        computed:{
            key(){
                return this.$route.fullPath;
            }
        }
    }
</script>
