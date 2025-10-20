(function(Ext, paqueteComunJS) {
    Ext.ns('Reingsys.util');
    Reingsys.util = {
        deshabilitarForma: function(padre) {
            Ext.iterate(padre.findByType('field'), function(f) {
                f.setReadOnly(true);
            });
            var deshabilitarBotones = function(p) {
                Ext.iterate(['Bottom', 'Top', 'Footer'], function(t) {
                    var tb = p['get' + t + 'Toolbar']();
                    if (tb) {
                        Ext.iterate(tb.findByType('button'), function(e) {
                            if ( !(e.etiquetas && e.etiquetas.ver) ) {
                                e.disable();
                            }
                        });
                    }
                });
            };
            deshabilitarBotones(padre);
            Ext.iterate(padre.findByType('panel'), deshabilitarBotones);
        },
        formatoNumero: function(val) {
            return paqueteComunJS.funcion.getNumeroFormateado(val);
        },
        textoLargo: function(value, metadata) {
            metadata.attr = 'style="white-space: normal;"';
            return value;
        }
    };
}(Ext, paqueteComunJS));
