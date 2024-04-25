<template>
  <div class="picture-uploader"
       :class="{'picture-uploader--big': big, 'picture-uploader--with-errors': errors && errors.length > 0}">
    <c-input-title>{{title}}</c-input-title>
    <div class="picture-uploader__container">
      <div class="picture-uploader__element" :class="{'picture-uploader__element--wide': wide}">
        <c-tooltip-icon icon="fa fa-exclamation-triangle" position="bottom-left" class="picture-uploader__error" v-if="errors && errors.length > 0">
          <div v-for="(error, $index) in errors" :key="$index">
            <c-translated-message :message="error"/>
          </div>
        </c-tooltip-icon>

        <div v-if="val" class="picture-uploader__reset" @click="reset()">
          <c-icon icon="close"/>
        </div>
        <input style="display:none" type="file" ref="fileinput" @change="onFileInputChange"/>
        <c-preloader block v-if="loading"/>
        <div v-if="!big && val && val.id > 0"
             :style="`background:url(${val.full_url}) no-repeat center center; background-size:contain;`"
             class="picture-uploader__img"></div>
        <img v-else-if="val && val.id > 0" :src="val.full_url" class="picture-uploader__img-block"/>
      </div>
      <div class="picture-uploader__texts">
        <c-button @click="fileInputClick()">{{ buttonText || $t('upload.select') }}</c-button>
      </div>
    </div>

  </div>
</template>
<style lang="scss">
.picture-uploader {
  display: flex;
  flex-direction: column;
  align-items: center;

  &__element {
    width: 7em;
    height: 7em;
    position: relative;
    background: var(--darken-5);
    text-align: center;
    border-radius: .25em;
    display: flex;
    align-items: center;
    justify-content: center;

    &--wide {
      width: 12.4em;
    }
  }

  &__reset {
    position: absolute;
    z-index: 1000000;
    opacity: .75;
    cursor: pointer;
    top: .5em;
    right: .5em;

    &:hover {
      opacity: .5;
    }
  }

  &__error {
    color: var(--negative-color);
    margin: 0;
    position: absolute;
    top: .75em;
    left: .75em;
    cursor: pointer;
  }


  &__img {
    background-size: contain !important;
    background-repeat: no-repeat;
    width: 100%;
    height: 100%;
  }

  &__img-block {
    max-width: 100%;
    max-height: 50em;
  }

  &__texts {
    margin: .5em 0 0;
  }



  &--big {
    width: 100%;
    display: block;
  }

  &--big &__container {
    margin: var(--vertical-margin) 0;
    display: flex;
    align-items: center;
    padding: 1em;
    border: 1px solid var(--input-border-color);
    background: var(--input-bg-color);
    flex: 1;
    border-radius: .25em;
  }


  &--big &__formats {
    display: block;
    font-size: .875em;
    margin: .5em 0 0;
  }

  &--big &__texts {
    margin: 0 0 0 1em;

    .button {
      display: inline-flex;
    }
  }
}
</style>
<script>

export default {
  props: {
    value: Object,
    title: String,
    buttonText: String,
    channel: Object,
    big: Boolean,
    wide: Boolean,
    folder: String,
    errors: {
      type: Array,
      required: false,
      default: () => {
        return []
      }
    },
  },
  data() {
    return {
      filename: '',
      loading: false,
      val: this.value,
    }
  },
  watch: {
    val(picture) {
      this.$emit('input', picture);
    }
  },
  methods: {
    reset() {
      this.val = {
        id: -1
      };
    },
    onFileInputChange(e) {
      let files = e.target.files;
      if (files && files[0]) {
        let picture = files[0];
        let re = /(?:\.([^.]+))?$/;
        let extension = re.exec(picture.name)[1];
        extension = extension.toLowerCase();
        let fd = new FormData();
        if (this.folder) {
          fd.append('folder', this.folder);
        }
        if (this.channel) {
          fd.append('channel_id', this.channel.id);
        }
        fd.append('picture', picture);
        if (this.id) {
          fd.append('id', this.id);
        }
        this.loading = true;
        this.$api.post('/upload/pictures', fd, {formData: true}).then(picture => {
          this.val = picture;
          this.$emit('update', picture.path);
        }).finally(() => {
          this.loading = false;
        })
      }
    },
    fileInputClick() {
      this.$refs.fileinput.click();
    }
  },
}
</script>
