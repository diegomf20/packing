import Vue from 'vue';
import VueRouter from 'vue-router'

Vue.use(VueRouter)

var routes =[
    { 
        path: '/', 
        component: require('./view/Home.vue').default
    },
    {
        path: '/acopio/new',
        component: require('./view/acopio/New.vue').default
    },
    {
        path: '/materia',
        component: require('./view/materia.vue').default
    },
    {
        path: '/impresora',
        component: require('./view/impresora.vue').default
    },
    {
        path: '/cliente',
        component: require('./view/cliente.vue').default
    },
    {
        path: '/variedad',
        component: require('./view/variedad.vue').default
    },
    {
        path: '/producto',
        component: require('./view/producto.vue').default
    },
    //Paletizado
    {
        path: '/paletizado/new',
        component: require('./view/paletizado/New.vue').default
    },
    {
        path: '/paletizado/:id',
        component: require('./view/paletizado/Count.vue').default
    },
    {
        path: '/paletizado',
        component: require('./view/paletizado/List.vue').default
    },
    {
        path: '/ini',
        component: require('./view/paletizado/List.vue').default
    },
    //ACOPIO
    {
        path: '/acopio/lote',
        component: require('./view/lote/Acopio.vue').default
    },
    {
        path: '/acopio/lote/new',
        component: require('./view/lote/New.vue').default
    },
    {
        path: '/acopio/lote/:id',
        component: require('./view/lote/sub-lote.vue').default
    },
    //Reportes
    {
        path: '/reporte/linea',
        component: require('./view/reporte/Linea.vue').default
    },
    {
        path: '/reporte/lote',
        component: require('./view/reporte/Lote.vue').default
    },
    {
        path: '/dispensador',
        component: require('./view/Dispensador.vue').default
    },

];
var router=new VueRouter({
    mode: 'history',
    routes,
    linkActiveClass: 'active'
});
export default router;