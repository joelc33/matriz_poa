// override 3.4.1.1 to fix Ext.define so it behaves the same as Ext.extend
// Fixes http://www.sencha.com/forum/showthread.php?278660
// Fixes http://www.sencha.com/forum/showthread.php?262945
Ext.define = function(className, body, createdFn) {
    var override = body.override,
        cls, extend, name, namespace;

    if (override) {
        delete body.override;
        cls = Ext.getClassByName(override);
        Ext.override(cls, body);
    } else {
        if (className) {
            namespace = Ext.createNamespace(className, true);
            name = className.substring(className.lastIndexOf('.') + 1);
        }

        // start of changes:

        extend = body.extend;
        if (extend) {
            delete body.extend;
            if (typeof extend == 'string') {
                extend = Ext.getClassByName(extend);
            }
        } else {
            extend = Ext.Base;
        }

        var oc = Object.prototype.constructor;
        cls = (body.constructor != oc) ? body.constructor : null;
        if (!cls) {
            cls = function() {
                extend.apply(this, arguments);
            };
        }

        // end of changes

        if (className) {
            cls.displayName = className;
        }
        cls.$isClass = true;
        cls.callParent = Ext.Base.callParent;

        if (typeof body == 'function') {
            body = body(cls);
        }

        Ext.extend(cls, extend, body);
        if (cls.prototype.constructor === cls) {
            // start of changes:          
            //delete cls.prototype.constructor;
            // end of changes
        }

        // Not extending a class which derives from Base...
        if (!cls.prototype.$isClass) {
            Ext.applyIf(cls.prototype, Ext.Base.prototype);
        }
        cls.prototype.self = cls;

        if (body.xtype) {
            Ext.reg(body.xtype, cls);
        }
        cls = body.singleton ? new cls() : cls;
        if (className) {
            namespace[name] = cls;
        }
    }

    if (createdFn) {
        createdFn.call(cls);
    }

    return cls;
};

