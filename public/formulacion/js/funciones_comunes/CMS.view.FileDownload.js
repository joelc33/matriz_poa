(function(Ext) {
    Ext.namespace('CMS.view');
    CMS.view.FileDownload = Ext.extend(Ext.Component, {
        alias: 'widget.FileDownloader',
        autoEl: {
            tag: 'iframe',
            cls: 'x-hidden',
            src: Ext.SSL_SECURE_URL
        },
        stateful: false,
        load: function(config) {
            var e = this.getEl();
            e.dom.src = config.url +
                (config.params ? '?' + Ext.urlEncode(config.params) : '');
            e.dom.onload = function() {
                if (e.dom.contentDocument.body.childNodes[0].wholeText == '404') {
                    Ext.Msg.show({
                        title: 'Archivo no encontrado',
                        msg: 'El documento solicitado no se encuentra en el servidor.',
                        buttons: Ext.Msg.OK,
                        icon: Ext.MessageBox.ERROR
                    });
                }
            };
        },
        init: function(config) {
            Ext.apply(this, Ext.apply(this.initialConfig, config));
            this.superclass.initComponent.apply(this, arguments);
        }
    });
    Ext.reg('CMS.view.FileDownload', CMS.view.FileDownload);
}(Ext));
