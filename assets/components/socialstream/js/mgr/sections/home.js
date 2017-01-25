Ext.onReady(function() {
    MODx.load({ xtype: 'socialstream-page-home'});
});

SocialStream.page.Home = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        components: [{
            xtype: 'socialstream-panel-home'
            ,renderTo: 'socialstream-panel-home-div'
        }]
    });
    SocialStream.page.Home.superclass.constructor.call(this,config);
};
Ext.extend(SocialStream.page.Home,MODx.Component);
Ext.reg('socialstream-page-home',SocialStream.page.Home);