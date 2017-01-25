var SocialHub = function(config) {
    config = config || {};
    SocialHub.superclass.constructor.call(this,config);
};
Ext.extend(SocialHub,Ext.Component,{
    page:{},window:{},grid:{},tree:{},panel:{},combo:{},config: {}
});
Ext.reg('socialhub',SocialHub);
SocialHub = new SocialHub();
