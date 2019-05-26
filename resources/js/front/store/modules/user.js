import {getInfo } from '@/api/user'
import { resetRouter } from '@/router/index'

const state = {
    name: '',
    introduction: '',
    avatar: '',
    roles:[]
}

const mutations = {
    SET_TOKEN: (state, token) => {
        state.token = token
    },
    SET_NAME: (state, name) => {
        state.name = name
    },
    SET_AVATAR: (state, avatar) => {
        if(avatar){
            state.avatar = avatar
        }
    },
    SET_ROLES: (state, roles) => {
        state.roles = roles
    },
    SET_INTRODUCTION: (state, introduction) => {
        state.introduction = introduction
    },
}

const actions = {

    // get user info
    getInfo({ commit, state }) {
        return new Promise((resolve, reject) => {
            getInfo().then(response => {
                const { data } = response

                if (!data) {
                    reject('Verification failed, please Login again.')
                }

                const { roles, name, avatar, introduction ,csrfToken } = data

                if (!roles || roles.length <= 0) {
                    reject('getInfo: roles must be a non-null array!')
                }
                if (!csrfToken) {
                    reject('csrfToken: csrfToken do not exist!')
                }
                commit('SET_ROLES', roles)
                commit('SET_NAME', name)
                commit('SET_AVATAR', avatar)
                commit('SET_INTRODUCTION', introduction)

                axios.defaults.headers.common = {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                };


                resolve(data)
            }).catch(error => {
                reject(error)
            })
        })
    },

    // Dynamically modify permissions
    changeRoles({ commit, dispatch }, role) {
        return new Promise(async resolve => {
            const { roles } = await dispatch('getInfo')

            resetRouter()

            // generate accessible routes map based on roles
            const accessRoutes = await dispatch('permission/generateRoutes', roles, { root: true })

            // dynamically add accessible routes
            router.addRoutes(accessRoutes)

            resolve()
        })
    }

}

export default {
    namespaced: true,
    state,
    mutations,
    actions
}

