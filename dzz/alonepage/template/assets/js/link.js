const Tmplink = {
    props:{
        model:{
            required:true,
            type: Object,
            default:{},
        },
        field:{
            required:true,
            type: Object,
            default:{},
        },
        ParenIndex:{
            required:true,
            type: Number,
            default:0,
        },
        typecollection:{
            required:true,
            type: Object,
            default:{},
        }
    },
    template:`
        <el-table ref="DomTable" :data="model.data[0].data" style="width: 100%" row-key="key">
            <el-table-column :label="Lang.text1" width="60" align="center">
                <template #default="scope">
                    <el-icon class="table-move-icon"><Rank /></el-icon>
                </template>
            </el-table-column>
            <el-table-column :label="Lang.text2" type="index" width="80" align="center" />
            <el-table-column :label="Lang.text3" width="60" align="center">
                <template #default="scope">
                    <el-upload
                        :show-file-list="false" 
                        name="files"
                        accept="image/gif,image/png,image/jpg,image/jpeg,image/svg"
                        action="index.php?mod=alonepage&op=alonepageinterface&do=upload" 
                        :on-success="handleUploadSucess">
                        <div @click="curRowIndex=scope.$index">
                            <img style="object-fit: contain;height: 32px;width: 32px;border-radius: var(--el-border-radius-small);" v-if="parseInt(scope.row.aid)" :src="scope.row.img" class="avatar" />
                            <el-button v-else plain icon="plus" size="small" style="padding: 0;height: 32px;width: 32px;"></el-button>
                        </div>
                    </el-upload>
                </template>
            </el-table-column>
            <el-table-column :label="Lang.text4" prop="title">
                <template #default="scope">
                    <el-input v-model="scope.row.title" />
                </template>
            </el-table-column>
            <el-table-column :label="Lang.text5" prop="message">
                <template #default="scope">
                    <el-input v-model="scope.row.message"></el-input>
                </template>
            </el-table-column>
            <el-table-column :label="Lang.text6" prop="link">

                <template #default="scope">
                    <div style="display:flex;">
                        <el-select v-model="scope.row.link" style="width: 110px;margin-right:6px;" @change="scope.row.linkval=''">
                            <el-option :label="Lang.text8" value="0"></el-option>
                            <el-option :label="Lang.text9" value="1"></el-option>
                            <el-option :label="Lang.text10" value="2"></el-option>
                            <el-option :label="Lang.text11" value="3"></el-option>
                        </el-select>
                        <template v-if="parseInt(scope.row.link) == 0">
                            <el-input v-model="scope.row.linkval"></el-input>
                        </template>
                        <template v-else-if="parseInt(scope.row.link) == 1">
                            <el-select v-model="scope.row.linkval" style="width: 100%">
                                <el-option v-for="item in typecollection.library" :label="item.appname" :value="item.appid"></el-option>
                            </el-select>
                        </template>
                        <template v-else-if="parseInt(scope.row.link) == 2">
                            <el-select v-model="scope.row.linkval" style="width: 100%">
                                <el-option v-for="item in typecollection.alonepage" :label="item.pagename" :value="item.id" :key="item.id"></el-option>
                            </el-select>
                        </template>
                        <template v-else-if="parseInt(scope.row.link) == 4">
                            <el-select v-model="scope.row.linkval" style="width: 100%">
                                <el-option v-for="item in typecollection.tab" :label="item.name" :value="item.gid" :key="item.gid"></el-option>
                            </el-select>
                        </template>
                        <template v-else-if="parseInt(scope.row.link) == 3">
                            <el-cascader 
                                style="width: 100%"
                                v-model="scope.row.linkval" 
                                :options="typecollection.banner"
                                :show-all-levels="false"
                                :emitPath="false"
                                :props="{value:'id',label:'bannername',checkStrictly:true}" 
                                clearable></el-cascader>
                        </template>
                    </div>
                </template>
            </el-table-column>
            <el-table-column width="80" align="center">
                <template #header>
                    <language 
                        v-if="model.data[0].langkey.tdata" 
                        :langkey="model.data[0].langkey.tdata"
                        @change="changeContent"
                        :other="typecollection"
                        ></language>
                </template>
                <template #default="scope">
                    <el-button type="danger" @click="handledelete(scope.$index)" icon="Delete" plain size="small"></el-button>
                </template>
            </el-table-column>
            <template #append>
                <el-button style="width: 100%;border-radius: 0;" plain text @click="handleadd" icon="plus">{{Lang.text7}}</el-button>
            </template>
        </el-table>
    `,
    setup(props,context){
        let Lang = {
            text1:__lang.sort,
            text2:__lang.index,
            text3:__lang.icon,
            text4:__lang.name,
            text5:__lang.desc,
            text6:__lang.Link_address,
            text7:__lang.add,
            text8:__lang.address,
            text9:__lang.library,
            text10:__lang.page,
            text11:__lang.column,
        };
        let curRowIndex = ref(null);//当前第几个上传
        let DomTable = ref(null);
        if(props.model.data && props.model.data.length && props.model.data[0].data.length){
            props.model.data[0].data.forEach(item => {
                item.key = getId();
            });
        }
        function handleadd(){//添加
            if(props.field[0].data && props.field[0].data.length){
                let val = JSON.parse(JSON.stringify(props.field[0].data[0]));
                val.key = getId();
                props.model.data[0].data.push(val);
            }
        };
        function getId(){  //获取随机数id
            let date = Date.now();
            let rund = Math.ceil(Math.random()*1000)
            let id = date + '' + rund;
            return id;
        };

        function handledelete(index){//删除
            props.model.data[0].data.splice(index,1);
        };
        function handleImgDelte(index){//图片删除
            props.model.data[0].data[index].aid = 0;
            props.model.data[0].data[index].img = '';
            props.model.data[0].data[index].title = '';
        };
        function handleUploadSucess(response, file, fileList){//上传成功
            if(response.files && response.files.length){
                let files = response.files[0];
                props.model.data[0].data[curRowIndex.value].aid = files.data.aid;
                props.model.data[0].data[curRowIndex.value].img = files.data.img;
            }

        };
        function changeContent(data){
            if(data && data.length){
                for (let index = 0; index < data.length; index++) {
                    const element = data[index];
                    if(props.model.data[0].data[index]){
                        props.model.data[0].data[index].aid = element.aid;
                        props.model.data[0].data[index].img = element.img;
                        props.model.data[0].data[index].title = element.title;
                        props.model.data[0].data[index].message = element.message;
                        props.model.data[0].data[index].link = element.link;
                        props.model.data[0].data[index].linkval = element.linkval;
                    }else{
                        props.model.data[0].data.push({
                            aid:element.aid,
                            img:element.img,
                            key:getId(),
                            title:element.title,
                            message:element.message,
                            link:element.link,
                            linkval:element.linkval
                        })
                    }
                }
                
            }else{
                props.model.data[0].data = [];
            }
            

        }
        onMounted(function(){//排序
            let tbody = DomTable.value.$el.querySelector('.el-table__body-wrapper tbody');
				Sortable.create(tbody, {
					handle: ".table-move-icon",
					animation: 150, // ms, number 单位：ms，定义排序动画的时间
					onUpdate: function(/**Event*/evt) {
						const currentRow = props.model.data[0].data.splice(evt.oldIndex, 1)[0];
                        props.model.data[0].data.splice(evt.newIndex, 0, currentRow);
					}
				})
        });
        return {
            Lang,
            handleadd,
            handledelete,
            DomTable,
            curRowIndex,
            handleUploadSucess,
            handleImgDelte,
            changeContent
        }
    }
}