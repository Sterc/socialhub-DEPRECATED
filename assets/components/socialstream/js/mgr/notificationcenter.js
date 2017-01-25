var SocialStream = function(config) {
    config = config || {};
SocialStream.superclass.constructor.call(this,config);
};
Ext.extend(SocialStream,Ext.Component,{
    page:{},window:{},grid:{},tree:{},panel:{},combo:{},config: {}
});
Ext.reg('socialstream',SocialStream);
SocialStream = new SocialStream();