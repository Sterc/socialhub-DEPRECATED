Ext.onReady(function() {
    MODx.load({ xtype: 'socialhub-page-home'});
});

SocialHub.page.Home = function(config) {
    config = config || {};
    Ext.applyIf(config,{
        components: [{
            xtype: 'socialhub-panel-home'
            ,renderTo: 'socialhub-panel-home-div'
        }]
    });
    SocialHub.page.Home.superclass.constructor.call(this,config);
};
Ext.extend(SocialHub.page.Home,MODx.Component);
Ext.reg('socialhub-page-home',SocialHub.page.Home);