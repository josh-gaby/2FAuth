import Vue from 'vue'
import Router from 'vue-router'

Vue.use(Router)

import Accounts         from './views/Accounts'
import Create           from './views/twofaccounts/Create'
import Edit             from './views/twofaccounts/Edit'
import Login            from './views/auth/Login'
import Register         from './views/auth/Register'
import PasswordUpdate   from './views/auth/password/Update'
import PasswordRequest  from './views/auth/password/Request'
import PasswordReset    from './views/auth/password/Reset'
import Profile         from './views/profile/Edit'
import NotFound         from './views/Error'

const router = new Router({
    mode: 'history',
    routes: [
        { path: '/', name: 'accounts', component: Accounts, props: true },
        { path: '/login', name: 'login',component: Login },
        { path: '/register', name: 'register',component: Register },
        { path: '/profile', name: 'profile',component: Profile },
        { path: '/create', name: 'create',component: Create },
        { path: '/edit/:twofaccountId', name: 'edit',component: Edit },

        { path: '/password/update', name: 'password.update',component: PasswordUpdate },
        { path: '/password/request', name: 'password.request', component: PasswordRequest },
        { path: '/password/reset/:token', name: 'password.reset', component: PasswordReset },

        { path: '/flooded', name: 'flooded',component: NotFound,props: true },
        { path: '/error', name: 'genericError',component: NotFound,props: true },
        { path: '/404', name: '404',component: NotFound,props: true },
        { path: '*', redirect: { name: '404' } }
    ],
});

export default router