import router from './router'
import store from './store'
import NProgress from 'nprogress' // progress bar
import 'nprogress/nprogress.css' // progress bar style
import getPageTitle from '@/utils/get-page-title'

NProgress.configure({ showSpinner: false }) // NProgress Configuration

router.beforeEach(async(to, from, next) => {
  // start progress bar
  NProgress.start()

  // set page title
  document.title = getPageTitle(to.meta.title)



   const hasRoles = store.getters.roles && store.getters.roles.length > 0
  // const hasGetUserInfo = store.getters.name
  if (hasRoles) {
    next()
  } else {
    try {
        const { roles } = await store.dispatch('user/getInfo')
        const accessRoutes = await store.dispatch('permission/generateRoutes', roles)
        console.log(accessRoutes)
        // dynamically add accessible routes
        router.addRoutes(accessRoutes)
      next({ ...to, replace: true })
    } catch (error) {
      NProgress.done()
    }
  }



})

router.afterEach(() => {
  // finish progress bar
  NProgress.done()
})
