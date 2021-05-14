<template>
<div class="filepicker" :class="{'filepicker--big': big}">
	<div class="filepicker__title">{{title}}</div>
  <div class="filepicker__container">
    <div class="filepicker__element">
      <div v-show="picture !== null || picturePath !== null" class="filepicker__reset" @click="reset()">
        <i class="material-icons">close</i>
      </div>
      <input style="display:none" type="file" ref="fileinput" @change="onFileInputChange" />
      <div v-show="status === -1" class="filepicker__preloader-container">
        <m-preloader :stroke-width="5" :circle-width="30" />
      </div>
      <div v-if="!big" :style="'background-image: url('+path+')'" class="filepicker__picture"></div>
      <img v-else-if="path && path !== ''" :src="path" class="filepicker__picture-block" />
    </div>
    <div class="filepicker__texts">
      <m-button @click="fileInputClick()">{{$t('upload.select')}}</m-button>
      <div class="filepicker__formats">{{$t('upload.formats', {formats: formatsListText})}}</div>
    </div>
  </div>

</div>
</template>
<style lang="scss">
.filepicker {
	margin: .5em 0;
  &__container {
    display: flex;
    align-items: center;
    padding: 1em;
    border: 1px solid rgba(255, 255, 255, .1);
  }
  &__preloader-container {
		position: absolute;
		top: 0;
		left: 0;
		width: 100%;
    z-index: 10000;
    height: calc(100% - 2em);
		display: flex;
		align-items: center;
		justify-content: center;
		background: rgba(255, 255, 255, 0.1);
	}
	&__element {
		position: relative;
    background: rgba(255, 255, 255, 0.1);
		width: 7em;
    height: 7em;
		text-align: center;
		border-radius: .25em;
	}
  &__reset{
    position: absolute;
    z-index: 1000000;
    opacity: .5;
    cursor: pointer;
    top: .5em;
    right: .5em;
  }
	&__title{
		margin: 0 0 .5em;
	}
	&__picture {
    width: 100%;
    height: 100%;
    margin: 0;
    background-size: contain;
    background-position: center;
    background-repeat: no-repeat;
	}
  &__picture-block {
    max-width: 100%;
    max-height: 50em;
  }
  &--big {
    width: 100%;
    align-items: flex-start;
  }

  &--big &__title{
   font-size: .75em;
  }

  &--big &__element {
    width: 100%;
  }

  &--big &__button-container {
    margin: 0 0 -.25em;
    padding: 0;
    font-size: 1.25em;
  }

  &__texts {
    max-width: calc(100% - 8em);
    margin: 0 0 0 1em;
  }

  &__formats {
    font-size: .875em;
    margin: .5em 0 0;
  }
}
</style>
<script lang="ts">
import Vue from 'vue'
import Component from 'vue-class-component'
import Picture from '~/types/Picture'
import { UploadPicture } from '~/api/modules/upload'
import { Watch } from '~/node_modules/vue-property-decorator'
enum uploadStatus {
  STATUS_NOT_STARTED,
  STATUS_UPLOADING,
  STATUS_SUCCESS,
  STATUS_ERROR
}


const PictureUploaderProps = Vue.extend({
  props: {
    big: {
      type: Boolean,
      required: false
    },
    errors: {
      type: Array,
      required: false,
      default() {
        return []
      }
    },

    value: {
      type: [Object, String],
      required: false
    },
    title:{
      type:[String],
      required: false,
    },
    returnPath: {
      type: Boolean,
      required: false
    },
    default: {
      type: String,
      required: false
    }
  }
})

const availablePictureExtensions = ['png', 'jpg', 'jpeg', 'gif'];

@Component({})
export default class PictureUploader extends PictureUploaderProps {
	filename: string = '';
	extensions: Array<string> = availablePictureExtensions;
  status: uploadStatus = uploadStatus.STATUS_NOT_STARTED;
  picture: Picture | null = null;
  picturePath: String | null = null;

  @Watch('value')
  onValueChanged(newValue: Picture | string | null) {
    if (this.returnPath) {
      this.picturePath = newValue && typeof newValue === 'object'  ? newValue!.full_url! : newValue;
    } else {
      this.picture = newValue as Picture;
    }
  }

  mounted() {
    if (this.value) {
      if (this.returnPath) {
        this.picturePath = this.value;
      } else {
        this.picture = this.value;
      }
    }
  }

  @Watch('picture')
  onPictureChanged(newVal: Picture | null) {
    if (!this.returnPath) {
      this.$emit('input', newVal);
    }
  }

  @Watch('picturePath')
  onPicturePathChanged(newVal: string | null) {
    if (this.returnPath) {
      this.$emit('input', newVal);
    }
  }


	get path() {
    return this.returnPath ? (this.picturePath) : (this.picture ? this.picture.full_url : null);
  }

	get formatsListText() {
	  return this.extensions.join(', ');
	};

  reset() {
    if (this.returnPath) {
      this.picturePath = this.default || null;
    } else {
      this.picture = null;
    }

  };

  onFileInputChange(e: Event) {
    let files = (<HTMLInputElement>e.target).files;
			if (files && files.length) {
				let picture = files[0];
				let re = /(?:\.([^.]+))?$/;
				let result = re.exec(picture.name);
				if (!result) {
          return;
        }
				let extension = result[1];
        extension = extension.toLowerCase();
				let extensions = this.extensions;
				if (extensions.indexOf(extension) !== -1) {
					this.status = uploadStatus.STATUS_UPLOADING;
					UploadPicture(picture).then((res) => {
            this.picture = res;
            this.picturePath = res.full_url;
            this.status = uploadStatus.STATUS_SUCCESS;
          }).catch(() => {
            this.status = uploadStatus.STATUS_ERROR;
          })
				} else {
          this.status = uploadStatus.STATUS_ERROR;
				}
			}
		}

		fileInputClick() {
      let fileInput: HTMLElement = this.$refs.fileinput as HTMLElement;
      fileInput.click();
		}

}
</script>
