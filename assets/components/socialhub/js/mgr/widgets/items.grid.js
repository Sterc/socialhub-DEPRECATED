SocialHub.grid.Items = function(config) {
    config = config || {};

    var defaultItemState = 1;

    Ext.applyIf(config,{
        id: 'socialhub-grid-items'
        ,url: SocialHub.config.connectorUrl
        ,baseParams: {
            action: 'mgr/item/getlist'
            ,active: defaultItemState
            ,source: config.source
            ,language: config.language
        }
        ,save_action: 'mgr/item/updatefromgrid'
        ,autosave: true
        ,fields: ['id','source','username', 'fullname', 'avatar', 'content', 'image', 'link', 'active', 'date']
        ,autoHeight: true
        ,paging: true
        ,remoteSort: true
        ,columns: [{
            header: _('id')
            ,dataIndex: 'id'
            ,width: 70
        },{
            header: _('socialhub.item.user')
            ,dataIndex: 'username'
            ,width: 200
            ,renderer: {
                fn: this.usernameRender,
                scope: this
            }
        },{
            header: _('socialhub.item.content')
            ,dataIndex: 'content'
            ,width: 250
            ,renderer: {
                fn: this.instaRender,
                scope: this
            }
        },{
            header: _('socialhub.item.date')
            ,dataIndex: 'date'
            ,dateFormat:'c'
            ,width: 100
            ,renderer: {
                fn: this.dateRender,
                scope: this
            } 
        }]
        ,tbar: ['->',
        {
            xtype: 'modx-combo'
            ,width:200
            ,store: new Ext.data.SimpleStore({
                data: [
                    [0, _('socialhub.status.denied')],
                    [1, _('socialhub.status.approved')],
                ],
                id: 0,
                fields: ["value", "text"]
            })
            ,mode: "local"
            ,valueField: "value"
            ,displayField: "text"
            ,value: defaultItemState
            ,listeners: {
                'select': {fn:this.filterApproved,scope:this}
            }
        }]
    });
    SocialHub.grid.Items.superclass.constructor.call(this,config);
};
Ext.extend(SocialHub.grid.Items,MODx.grid.Grid,{
    windows: {}
     ,filterApproved: function(combo) {
        var s = this.getStore();
        s.baseParams.active = combo.getValue();
        this.getBottomToolbar().changePage(1);
        this.refresh();
    }
    ,getMenu: function() {
        var m;

        if (this.menu.record.active === false) {
            m = [{
                text: _('socialhub.approve')
                ,handler: this.setActive
            }];
        } else{
            m = [{
                text: _('socialhub.deny')
                ,handler: this.setInactive
            }];
        }
        this.addContextMenuItem(m);
    }
    ,updateItem: function(btn,e,isUpdate) {
        if (!this.menu.record || !this.menu.record.id) return false;

        var updateItem = MODx.load({
            xtype: 'socialhub-window-item'
            ,title: _('socialhub.item.update')
            ,action: 'mgr/item/update'
            ,record: this.menu.record
            ,listeners: {
                'success': {fn:function() { this.refresh(); },scope:this}
            }
        });

        updateItem.fp.getForm().reset();
        updateItem.fp.getForm().setValues(this.menu.record);
        updateItem.show(e.target);
    }
    ,removeItem: function(btn,e) {
        if (!this.menu.record) return false;
        
        MODx.msg.confirm({
            title: _('socialhub.item.remove')
            ,text: _('socialhub.item.remove_confirm')
            ,url: this.config.url
            ,params: {
                action: 'mgr/item/remove'
                ,id: this.menu.record.id
            }
            ,listeners: {
                'success': {fn:function(r) { this.refresh(); },scope:this}
            }
        });
    }
    ,setActive: function(btn,e) {
        MODx.msg.confirm({
            title: _('socialhub.approve')
            ,text: _('socialhub.msg.approve')
            ,url: this.config.url
            ,params: {
                action: 'mgr/item/update'
                ,id: this.menu.record.id
                ,active: 1
            }
            ,listeners: {
                'success': {fn:function(r) { this.refresh(); },scope:this}
            }
        });
    }
    ,setInactive: function(btn,e) {
        MODx.msg.confirm({
            title: _('socialhub.approve')
            ,text: _('socialhub.msg.deny')
            ,url: this.config.url
            ,params: {
                action: 'mgr/item/update'
                ,id: this.menu.record.id
                ,active: 0
            }
            ,listeners: {
                'success': {fn:function(r) { this.refresh(); },scope:this}
            }
        });
    }
    ,search: function(tf,nv,ov) {
        var s = this.getStore();
        s.baseParams.query = tf.getValue();
        this.getBottomToolbar().changePage(1);
        this.refresh();
    }
    ,usernameRender: function (value, metaData, record, rowIndex, colIndex, store) {
        var socialLink = 'https://instagram.com/';
        if(record.data.source == 'twitter'){
            socialLink = 'https://twitter.com/';
        }

        if (record.data.avatar != '') {
            var tpl = new Ext.XTemplate('<tpl for=".">' + '<img src="{avatar}" width="75" style="float:left; margin-right:10px"/><h3>{fullname}</h3><p><a href="{link}" target="_blank">{username}</a></p>' + '</tpl>', {
                compiled: true
            });
        } else {
            var tpl = new Ext.XTemplate('<tpl for=".">' + '<h3>{fullname}</h3><p><a href="{link}" target="_blank">{username}</a></p>' + '</tpl>', {
                compiled: true
            });
        }

      return tpl.apply(record.data);
   }    
   ,instaRender: function (value, metaData, record, rowIndex, colIndex, store) {
        if(record.data.image.length == 0){
            var tpl = new Ext.XTemplate('<tpl for=".">' + '<p>{content}</p>' + '</tpl>', {
                compiled: true
            });          
        }
        else {
            var tpl = new Ext.XTemplate('<tpl for=".">' + '<img src="{image}" width="200"/><p>{content}</p>' + '</tpl>', {
                compiled: true
            });
        }
        return tpl.apply(record.data);
   },
});
Ext.reg('socialhub-grid-items',SocialHub.grid.Items);
