// resources/js/primevue-types.d.ts
declare module 'primevue/config' {
    import { Plugin } from 'vue';
    const PrimeVue: Plugin;
    export default PrimeVue;
}

declare module 'primevue/toolbar' {
    import { Component } from 'vue';
    const Toolbar: Component;
    export default Toolbar;
}

declare module 'primevue/button' {
    import { Component } from 'vue';
    const Button: Component;
    export default Button;
}

declare module 'primevue/datatable' {
    import { Component } from 'vue';
    const DataTable: Component;
    export default DataTable;
}

declare module 'primevue/column' {
    import { Component } from 'vue';
    const Column: Component;
    export default Column;
}

declare module 'primevue/inputtext' {
    import { Component } from 'vue';
    const InputText: Component;
    export default InputText;
}

// Add other PrimeVue components as needed
declare module 'primevue/*' {
    import { Component } from 'vue';
    const Component: Component;
    export default Component;
}