const screenclassify = {
	template: `<van-collapse v-model="parammodel.activeNames" :border="false">
					<van-collapse-item v-for="item in paramdata" :key="item.fid" :name="item.fid" disabled  :class="{'active':parammodel.value.indexOf(item.fid)>-1}" :ref="'collapse_'+item.fid">
						<template #title>
							<div  @click.stop="ScreenClassifyClick(item)">
								{{item.fname}}
							</div>
						</template>
						<template #right-icon>
							<template v-if="!item.leaf">
								<i @click.stop="ScreenClassifyToggle(item)" class="van-icon van-icon-arrow van-cell__right-icon"></i>
							</template>
							<template v-else>
								<div style="width: 20px;"></div>
							</template>
						</template>
						<div class="van-cell" v-if="item.loading">
							<van-loading type="spinner" size="24px" />
						</div>
						<template v-if="item.children && item.children.length">
							<screenclassify :parammodel="parammodel" :paramdata="item.children" @screenclassifychange="screenclassifychange" :alldata="alldata" :expandedkeys="expandedkeys"></screenclassify>
						</template>
					</van-collapse-item>
				</van-collapse>`,
	props:['paramdata','parammodel','alldata','expandedkeys'],
	data: function() {
		return {
			parentdata:[],
			parentthat:{},
			parentFid:'',
			parentPfid:true,
		}
	},
	props:{
		isadmin:{
			required:false,
			type: Number,
			default:0,
		},
	},
	created() {
		
	},
	methods:{
		ScreenClassifyClick(item){
			var self = this;
			var classify = this.parammodel.value;
			var index = classify.indexOf(item.fid);
			if(index> -1){
				classify.splice(index,1);
				this.ScreenClassifyDelChildFid(item.children)
				var pathKeys = item.pathkey.split(item.appid);
				if(pathKeys.length>1){
					for(var k in pathKeys){
						var kitem = pathKeys[k]+item.appid;
						var kindex = classify.indexOf(kitem);
						if(kindex>-1){
							classify.splice(kindex,1);
						}
					}
				}
			}else{
				classify.push(item.fid);
				this.ScreenClassifyAddChildFid(item.children);
				this.ScreenClassifyAddParentFid(item,self.paramdata);
			}
			self.$emit('screenclassifychange');
		},
		ScreenClassifyAddChildFid(data){
			var classify = this.parammodel.value;
			for(var i in data){
				var item = data[i];
				if(classify.indexOf(item.fid)<0){
					classify.push(item.fid)
				}
				if(item.children && item.children.length){
					this.ScreenClassifyAddChildFid(item.children);
				}
			}
		},
		ScreenClassifyDelChildFid(data){
			var classify = this.parammodel.value;
			for(var i in data){
				var item = data[i];
				var index = classify.indexOf(item.fid);
				if(index>-1){
					classify.splice(index,1);
				}
				if(item.children && item.children.length){
					this.ScreenClassifyDelChildFid(item.children);
				}
			}
		},
		ScreenClassifyAddParentFid(that,data){
			var status = false;
			var classify = this.parammodel.value;
			if(data && data.length){
				for(var i in data){
					if(classify.indexOf(data[i].fid)<0){
						status = false;
						break;
					}else{
						status = true;
					}
				}
			}else{
				status = true;
			}
			
			if(status && that.pfid){
				if(classify.indexOf(that.pfid)<0){
					classify.push(that.pfid);
				}
				this.parentdata = [];
				this.parentthat = {};
				this.ScreenClassifyGetPfid(this.alldata,that.pfid);
				this.ScreenClassifyGetFidData(this.alldata,this.parentFid);
				this.ScreenClassifyAddParentFid(this.parentthat,this.parentdata);
				
			}
		},
		ScreenClassifyGetPfid(data,fid){
			for(var i in data){
				var item = data[i];
				if(item.fid == fid){
					this.parentthat = item;
					if(item.pfid){
						this.parentFid = item.pfid;
					}else{
						this.parentFid = item.fid;
						this.parentPfid = false;
					}
					return false;
				}else{
					if(item.children && item.children.length){
						this.ScreenClassifyGetPfid(item.children,fid);
					}
				}
			}
		},
		ScreenClassifyGetFidData(data,fid){
			for(var i in data){
				var item = data[i];
				if(item.fid == fid){
					this.parentdata = item.children;
					return false;
				}else{
					if(item.children && item.children.length){
						this.ScreenClassifyGetFidData(item.children,fid);
					}
				}
			}
		},
		screenclassifychange(){
			var self = this;
			self.$emit('screenclassifychange');
		},
		ScreenClassifyToggle(item){
			var self = this;
			
			var fid = item.fid;
			
			if(self.$refs['collapse_'+fid] && self.$refs['collapse_'+fid].length){
				var expanded = self.$refs['collapse_'+fid][0].expanded;
				if(!expanded && !item.loaded){
					self.ScreenGetdataClassify(item);
				}
				self.$nextTick(function(){
					setTimeout(function(){
						self.$refs['collapse_'+fid][0].toggle();
					},100)
				});
				
			}
		},
		async ScreenGetdataClassify(data){
			var self = this;
			var param = {
				appid: data.appid,
				pfids:data.fid,
			};
			let res = await axios.post(MOD_URL+'&op=ajax&operation=getsearchfolder',param);
			if(res == 'intercept'){
				return false;
			}
			var json = res.data;
			var arr = [];
			for(var i in json.folderdatanum){
				var item = json.folderdatanum[i];
				item['loaded'] = false;
				item['loading'] = true;
				item['children'] = [];
				arr.push(item);
				if(self.parammodel.value.indexOf(item.pfid)>-1){
					self.parammodel.value.push(item.fid);
				}
			}
			data.children = arr;
			data.loaded = true;
			data.loading = false;
		},
	},
	mounted() {
		var self = this;
		self.$nextTick(function(){
			for(var i in this.paramdata){
				var item = this.paramdata[i];
				if(this.expandedkeys.indexOf(item.fid)>-1){
					this.ScreenClassifyToggle(item);
				}
			}
		});
	},
};