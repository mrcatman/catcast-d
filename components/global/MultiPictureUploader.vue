<template>
  <c-box class="multi-picture-uploader" :class="{'multi-picture-uploader--small': config.small}">
    <template slot="title">
      {{ config.title }}
    </template>
    <template slot="title_buttons">
      <c-button v-show="val.length < config.max" icon="fa-plus-square" @click="fileInputClick">
        {{ $t('multi_picture_upload.select_files') }}
      </c-button>
    </template>
    <template slot="main" v-if="val">
      <input @change="onFileInputChange" type="file" multiple ref="fileinput" style="display:none"/>
      <div v-if="config.description" class="multi-picture-uploader__description">
        {{ config.description }}
      </div>
      <div class="multi-picture-uploader__files" v-dragula="val" drake="val">
        <div class="multi-picture-uploader__file" :key="$index" v-for="(picture, $index) in val">
          <a class="multi-picture-uploader__file__delete" @click="deletePicture(picture)">
            <i class="material-icons">close</i>
          </a>
          <c-preloader block v-if="picture.uploading"/>
          <img :src="picture.full_url" class="multi-picture-uploader__file__preview" v-else/>
          <c-input v-if="config.nameField && config.nameField.use" class="multi-picture-uploader__file__name"
                   :placeholder="config.nameField.title" v-model="picture[config.nameField.id]"/>
        </div>
      </div>
    </template>
  </c-box>

</template>
<style lang="scss">
.multi-picture-uploader {
  --vertical-margin: 0;
  margin-top: 1em;
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
    margin-bottom: 1em;
  }

  &__file {
    text-align: center;
    border-radius: .25em;
    margin: 0 .5em .5em 0;
    min-width: 7em;
    position: relative;
    padding: .5em;
    background: rgba(255, 255, 255, .05);

    &__name {
      width: 100%;
    }

    &__preloader {
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    &__preview {
      height: 7em;
      margin-bottom: 1em;
    }

    &__delete {
      position: absolute;
      z-index: 10;
      opacity: .75;
      cursor: pointer;
      top: .5em;
      right: .5em;

      &:hover {
        opacity: .5;
      }
    }
  }

  &__files {
    padding: 1em 1em .5em;
    border: 2px solid rgba(255, 255, 255, .1);
    background: var(--box-color);
    border-radius: .25em;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
  }

  &--small &__file {
    min-width: unset;
    width: calc(12.5% - 1.5em);

    &__preview {
      height: unset;
      width: 2.5em;
    }
  }

  &--small &__files {
    max-height: 20em;
    overflow: auto;
  }
}
</style>
<script>
import {mapGetters} from "vuex";

export default {
  props: {
    config: {
      type: Object,
      required: true,
    },
    value: Array,
  },
  data() {
    return {
      val: this.value || [],
    }
  },
  watch: {
    value(newValue) {
      this.pictures = newValue;
    },
    val: {
      handler(pictures) {
        this.$emit('input', pictures.filter(picture => !!picture.full_url).map(picture => {
          let data = {
            id: picture.id,
          }
          if (this.config.nameField && this.config.nameField.use && picture[this.config.nameField.id]) {
            data[this.config.nameField.id] = picture[this.config.nameField.id];
          }
          return data;
        }));
      },
      deep: true
    }
  },
  methods: {
    deletePicture(picture) {
      this.val.splice(this.val.indexOf(picture), 1);
    },
    onFileInputChange(e) {
      let files = e.target.files;
      if (files) {
        for (let picture of files) {
          if (this.val.length < this.config.max) {
            let re = /(?:\.([^.]+))?$/;
            let ext = re.exec(picture.name)[1];
            let fd = new FormData();
            if (this.config.folder) {
              fd.append('folder', this.config.folder);
            }
            fd.append('picture', picture);
            let pictureData = {
              uploading: true,
            };
            this.val.push(pictureData);
            this.$api.post('/upload/pictures', fd, {formData: true}).then(picture => {
              pictureData.id = picture.id;
              pictureData.full_url = picture.full_url;
              pictureData.uploading = false;
            }).finally(() => {
              this.loading = false;
            })
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
