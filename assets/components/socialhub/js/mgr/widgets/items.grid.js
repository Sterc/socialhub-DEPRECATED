SocialHub.grid.Items = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        id: 'socialhub-grid-items'
        ,url: SocialHub.config.connectorUrl
        ,baseParams: {
            action: 'mgr/item/getlist'
            ,active: 0
            ,source: config.source
            ,language: config.language
        }
        ,save_action: 'mgr/item/updatefromgrid'
        ,autosave: true
        // ,fields: ['id','username', 'fullname', 'avatar','content', 'image','type', 'link', 'approved', 'date']
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
        ,tbar: [
        // {
        //     text: _('socialhub.item.create')
        //     ,handler: this.createItem
        //     ,scope: this
        // },
        '->',
        // {
        //     xtype: 'textfield'
        //     ,emptyText: _('socialhub.global.search') + '...'
        //     ,listeners: {
        //         'change': {fn:this.search,scope:this}
        //         ,'render': {fn: function(cmp) {
        //             new Ext.KeyMap(cmp.getEl(), {
        //                 key: Ext.EventObject.ENTER
        //                 ,fn: function() {
        //                     this.fireEvent('change',this);
        //                     this.blur();
        //                     return true;
        //                 }
        //                 ,scope: cmp
        //             });
        //         },scope:this}
        //     }
        // }
        {
            xtype: 'modx-combo'
            ,width:200
            ,store: new Ext.data.SimpleStore({
                data: [
                    [0, 'Niet goedgekeurd'],
                    [1, 'Wel goedgekeurd'],
                ],
                id: 0,
                fields: ["value", "text"]
            })
            ,mode: "local"
            ,valueField: "value"
            ,displayField: "text"
            ,value: 0
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
        console.log(this.menu.record);
        if(this.menu.record.active === false){
            m = [{
                text: _('socialhub.approve')
                ,handler: this.setActive
            }];
        }else{
            m = [{
                text: _('socialhub.deny')
                ,handler: this.setInactive
            }];
        }
        this.addContextMenuItem(m);
    }
    // ,createItem: function(btn,e) {

    //     var createItem = MODx.load({
    //         xtype: 'socialhub-window-item'
    //         ,listeners: {
    //             'success': {fn:function() { this.refresh(); },scope:this}
    //         }
    //     });

    //     createItem.show(e.target);
    // }
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

      var tpl = new Ext.XTemplate('<tpl for=".">' + '<img src="{avatar}" width="75" style="float:left; margin-right:10px"/><h3>{fullname}</h3><p><a href="{link}" target="_blank">{username}</a></p>' + '</tpl>', {
         compiled: true
      });
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

// SocialHub.window.Item = function(config) {
//     config = config || {};
//     Ext.applyIf(config,{
//         title: _('socialhub.item.create')
//         ,closeAction: 'close'
//         ,url: SocialHub.config.connectorUrl
//         ,action: 'mgr/item/create'
//         ,fields: [{
//             xtype: 'textfield'
//             ,name: 'id'
//             ,hidden: true
//         },{
//             xtype: 'textfield'
//             ,fieldLabel: _('name')
//             ,name: 'name'
//             ,anchor: '100%'
//         },{
//             xtype: 'textarea'
//             ,fieldLabel: _('description')
//             ,name: 'description'
//             ,anchor: '100%'
//         },{
//             xtype: 'textfield'
//             ,name: 'position'
//             ,hidden: true
//         }]
//     });
//     SocialHub.window.Item.superclass.constructor.call(this,config);
// };
// Ext.extend(SocialHub.window.Item,MODx.Window);
// Ext.reg('socialhub-window-item',SocialHub.window.Item);

