<template>
	<c-modal v-model="data.visible" :showCloseButton="true" :header="$t('dashboard.tracks.add_folder._title')">
		<div slot="main">
			<c-response :data="response" />
			<div class="modal__input-container">
				<c-input :errors="errors.folder_title" :title="$t('dashboard.tracks.add_folder.folder_title')" v-model="folderData.folder_title" />
			</div>
			<div class="modal__input-container">
				<c-input type="textarea" :errors="errors.folder_description" :title="$t('dashboard.tracks.add_folder.folder_description')" v-model="folderData.folder_description" />
			</div>
		</div>
		<div class="modal__buttons" slot="buttons">
			<div class="buttons-row">
				<c-button :loading="loading"  @click="addFolder()">{{$t('global.ok')}}</c-button>
				<c-button color="red" @click="data.visible = false">{{$t('global.cancel')}}</c-button>
			</div>
		</div>
	</c-modal>
</template>
<script>
export default {
	props: {
		folderId: {
			type:[Number],
			required:true
		},
		channel: {
			type:[Object],
			required:true
		},
		value: {
			type:[Object],
			required:true
		},
	},
	data() {
		return {
			errors: {},
			data:this.value,
			loading:false,
			folderData: {
				folder_title: '',
				folder_description: '',
			},
			response:null
		}
	},
	watch: {
	  'data.visible'(oldVal,newVal) {
      if (newVal && !oldVal) {
        this.folderData.folder_title = '';
        this.folderData.folder_description = '';
        this.response = null;
      }
    },
		data(newData,oldData) {
			this.$emit('input',newData);
		}
	},
	methods: {
		addFolder() {
			this.loading = true;
			let data = this.folderData;
			if (!data.is_editing) {
				data.channel_id = this.channel.id;
				data.parent_id = this.folderId;
				this.$axios.post('/folders',data).then(res=>{
					this.loading = false;
					this.response = res.data;
					this.errors = res.data.errors || {};
					if (res.data.status) {
						this.$store.commit('NEW_ALERT',res.data);
						this.$emit('newfolder',res.data.folder);
						this.data.visible = false;
					}
				});
			} else {
				this.$axios.post('/folders',data).then(res=>{
					this.loading = false;
					this.response = res.data;
					this.errors = res.data.errors || {};
					if (res.data.status) {
						this.$store.commit('NEW_ALERT',res.data);
						this.$emit('editfolder',res.data.folder);
						this.data.visible = false;
					}
				});
			}
		}
	}
}
</script>
