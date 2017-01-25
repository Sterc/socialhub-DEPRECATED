SocialStream.panel.Home = function(config) {
    config = config || {};
    Ext.apply(config,{
        border: false
        ,baseCls: 'modx-formpanel'
        ,cls: 'container'
        ,items: [{
            html: '<h2>'+_('socialstream')+'</h2>'
            ,border: false
            ,cls: 'modx-page-header'
        },{
            xtype: 'modx-tabs'
            ,defaults: { border: false ,autoHeight: true }
            ,border: true
            ,activeItem: 0
            ,hideMode: 'offsets'
            ,items: [{
                title: _('socialstream.item.items')
                ,items: [{
                    html: '<p>'+_('socialstream.item.intro_msg')+'</p>'
                    ,border: false
                    ,bodyCssClass: 'panel-desc'
                },{
                    xtype: 'modx-vtabs'
                    ,items:[{
                        title: 'Instagram'
                        ,items:[{
                            xtype: 'socialstream-grid-items'
                            ,source: 'instagram'
                            ,id: 'insta'
                            ,preventRender: true
                            ,cls: 'main-wrapper'
                        }]
                    },{
                        title: 'NL - Twitter'
                        ,items:[{
                            xtype: 'socialstream-grid-items'
                            ,source: 'twitter'
                            ,language: 'nl'
                            ,id: 'twitter-nl'
                            ,preventRender: true
                            ,cls: 'main-wrapper'
                        }]
                    },{
                        title: 'EN - Twitter'
                        ,items:[{
                            xtype: 'socialstream-grid-items'
                            ,source: 'twitter'
                            ,language: 'en'
                            ,id: 'twitter-en'
                            ,preventRender: true
                            ,cls: 'main-wrapper'
                        }]
                    },{
                        title: 'DE - Twitter'
                        ,items:[{
                            xtype: 'socialstream-grid-items'
                            ,source: 'twitter'
                            ,language: 'de'
                            ,id: 'twitter-de'
                            ,preventRender: true
                            ,cls: 'main-wrapper'
                        }]
                    }
                    ]
                }]

            }]
        }]
    });
    SocialStream.panel.Home.superclass.constructor.call(this,config);
};
Ext.extend(SocialStream.panel.Home,MODx.Panel);
Ext.reg('socialstream-panel-home',SocialStream.panel.Home);