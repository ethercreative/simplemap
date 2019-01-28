export function t (message : string, params : any = null) {
    // @ts-ignore
    return window.Craft.t('simplemap', message, params);
}
