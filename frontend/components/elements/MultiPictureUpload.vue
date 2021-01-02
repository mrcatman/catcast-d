<template>
<div class="multi-picture-upload">
	<input @change="onFileInputChange" type="file" multiple ref="fileinput" style="display:none"/>
  <div class="multi-picture-upload__header">
    <div class="multi-picture-upload__header__title">
      {{title}}
    </div>
    <m-btn v-show="pictures.length < max" faIcon="plus-square" @click="fileInputClick">
      {{$t('multi_picture_upload.select_files')}}
    </m-btn>
  </div>
  <div v-if="description" class="multi-picture-upload__description">
   {{description}}
  </div>
  <div class="multi-picture-upload__files" v-dragula="pictures" drake="pictures">
    <div class="multi-picture-upload__file" :key="$index" v-for="(picture, $index) in pictures">
      <a class="multi-picture-upload__file__delete" @click="deletePicture(picture)">
        <i class="fa fa-times"></i>
      </a>
      <div class="multi-picture-upload__file__preloader" v-if="picture.isUploading">
        <m-preloader stroke-width="5" :circle-width="'2.5em'" />
      </div>
      <img :src="picture.data.full_path" class="multi-picture-upload__file__preview" v-else/>
    </div>
  </div>
</div>
</template>
<style lang="scss">
.multi-picture-upload {
  box-shadow: 0 .5em 1.75em -.75em var(--active-color);
  padding: 0 0 1em;
  margin: 0 0 1em;
  &__button {
    background: var(--active-color);
    height: 2em;
    width: 2em;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5em;
    border-radius: .125em;
    cursor: pointer;
  }
  &__description {
    background: var(--box-footer-color);
    padding: 1em;
  }
  &__file {
    text-align: center;
    border-radius: .25em;
    margin: 0 .5em .5em 0;
    min-width: 7em;
    height: 7em;
    position: relative;
    padding: .5em;
    background: rgba(255, 255, 255, 0.25);
    &__preloader {
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    &__preview {
      height: 100%;
    }
    &__delete {
      position: absolute;
      top: .25em;
      right: .25em;
      z-index: 1000;
      background: var(--negative-color);
      width: 1em;
      height: 1em;
      text-align: center;
      line-height: 1;
      padding: .25em;
      border-radius: .25em;
      opacity: .85;
      cursor: pointer;
    }
  }

  &__files {
    margin: 0 1em;
    border: 3px dashed #ffffff40;
    background: var(--box-color);
    border-radius: .25em;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    padding: 1em;
  }

  &__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: var(--box-header-color);
    padding: .5em 1em;
    @media screen and (max-width: 768px) {
      display: block;
    }
    &__title {
      font-weight: 500;
      color: var(--active-color);
    }
  }

}
</style>
<script>

export default {
	props:{
	  description: {
	    type: [String],
      required: false,
    },
	  title: {
	    type: [String],
      required: false
    },
	  max: {
	    type: [Number],
      required: false,
      default: 10
    },
		folder:{
			type: [String],
			required: false,
		},
		value:{
	    type: [Array],
      required: true,
    }
	},
	data() {
		return {
			filename: '',
			extensions: ['png', 'jpg', 'jpeg', 'gif'],
      pictures: this.value,
		}
	},
	computed:{
		getFormatsList() {
			return this.extensions.join(', ');
		}
	},
	watch:{
	  value(newValue) {
	    this.pictures = newValue;
    },
    pictures(newVal) {
	    //let pictures = JSON.parse(JSON.stringify(newVal)).filter(picture => picture.status === 1);
		  this.$emit('input', newVal);
		}
	},
	mounted() {

	},
	methods: {
    deletePicture(picture) {
      this.pictures.splice(this.pictures.indexOf(picture), 1);
    },
	  onFileInputChange(e) {
			let files = e.target.files;
			if (files) {
				for (let index in files) {
				  if (this.pictures.length < this.max) {
            if (index !== 'length') {
              let image = files[index];
              let re = /(?:\.([^.]+))?$/;
              let ext = re.exec(image.name)[1];
              if (ext) {
                let extensions = this.extensions;
                if (extensions.indexOf(ext) !== -1) {
                  let fd = new FormData();
                  if (this.folder) {
                    fd.append('folder', this.folder);
                  }
                  fd.append('picture', image);
                  let pictureData = {
                    isUploading: true,
                    data: null
                  };
                  this.pictures.push(pictureData);
                  this.$axios.post('/upload/pictures', fd, {headers: {'Content-Type': 'multipart/form-data'}}).then((res) => {
                    if (res.data.status) {
                      pictureData.isUploading = false;
                      pictureData.data = res.data.picture;

                    } else {
                      this.$store.commit('NEW_ALERT', res.data);
                      this.pictures.splice(this.pictures.indexOf(pictureData), 1);
                    }
                  }).catch((err) => {
                    // pictureData.status = 0;
                    this.pictures.splice(this.pictures.indexOf(pictureData), 1);
                    this.$store.commit('NEW_ALERT',
                      {
                        'no_translate': true,
                        'status': 0,
                        'text': this.$t('upload._errors.upload_error') + err.toString()
                      }
                    );
                  })
                } else {
                  this.$store.commit(
                    'NEW_ALERT',
                    {
                      'no_translate': true,
                      'status': 0,
                      'text': this.$t('upload._errors.format_wrong') + this.getFormatsList
                    }
                  );
                }
              }
            }
          }
        }
			}
		},
		fileInputClick() {
			this.$refs.fileinput.click();
		}
	}
}
</script>
