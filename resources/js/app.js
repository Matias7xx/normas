require('./bootstrap');
import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';

createInertiaApp({
  title: title => `${title} - Sistema Doc PCPB`,
  resolve: name => {
    const pages = import.meta.glob('./Public/Pages/**/*.vue', { eager: true });
    return pages[`./Public/Pages/${name}.vue`];
  },
  setup({ el, App, props, plugin }) {
    return createApp({ render: () => h(App, props) })
      .use(plugin)
      .mount(el);
  },
  progress: {
    color: '#bea55a',
  },
});
