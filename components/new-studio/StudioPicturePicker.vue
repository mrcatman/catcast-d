<template>
  <div class="studio-picture-picker">
    <c-picture-uploader :buttonText="$t('new_studio.picture.upload')" :channel="channel" :returnData="true" :big="true" folder="overlays" :title="$t(input.name)" v-model="val" />
    <c-button big color="green" @click="openPanel">
      {{$t('new_studio.picture.select')}}
    </c-button>

    <c-modal v-model="picturesPanel.visible" :header="$t('new_studio.picture.select')">
      <div slot="main">
        <div class="studio-picture-picker__preloader" v-if="picturesPanel.loading">
          <c-preloader block  />
        </div>

        <div class="grid" v-else>
          <div class="grid-item" v-for="(item, $index) in picturesPanel.list" :key="$index">
            <a @click="selectPicture(item)">
              <img class="grid-item__picture" :src="item.full_url"/>
            </a>
            <div class="grid-item__buttons">
              <c-button flat @click="openDeletePanel(item)">{{$t('global.delete')}}</c-button>
            </div>
          </div>
        </div>
      </div>
    </c-modal>


    <c-modal v-model="deletePanel.visible">
      <div slot="main">
        <div class="modal__text">
          {{$t('global.are_you_sure')}}
        </div>
      </div>
      <div class="modal__buttons" slot="buttons">
        <div class="buttons-row">
          <c-button :loading="deletePanel.loading" @click="deletePicture()">{{$t('global.ok')}}</c-button>
          <c-button flat @click="deletePanel.visible = false">{{ $t('global.cancel')}}</c-button>
        </div>
      </div>
    </c-modal>


  </div>
</template>
<style lang="scss">
  .studio-picture-picker {
    &__preloader {
      padding: 2.5em 0;
      position: relative;
    }
  }
</style>
<script>
  export default  {
      props: ['value', 'input', 'channel'],
      methods: {
          deletePicture() {
              this.deletePanel.loading = true;
              this.$axios.delete(`overlays/media/${this.deletePanel.picture.id}`).then(res => {
                  this.$store.commit("NEW_ALERT", res.data);
                  if (res.data.status) {
                      this.deletePanel.loading = false;
                      this.deletePanel.visible = false;
                      this.picturesPanel.list.splice(this.picturesPanel.list.map(picture => picture.id).indexOf(res.data.data.picture.id), 1);
                      if (this.val && this.val.path === res.data.data.picture.full_url) {

                          this.val = null;
                      }
                  }
              })
          },

          openDeletePanel(picture) {
              this.deletePanel.picture = picture;
              this.deletePanel.visible = true;
          },
          selectPicture(picture) {
              this.val = {id: picture.id, path: picture.full_url};
              this.picturesPanel.visible = false;
          },
          load() {
              this.picturesPanel.loading = true;
              this.$axios.get(`overlays/media/getbychannel/${this.channel.id}`).then(res => {
                  this.picturesPanel.list = res.data.data.pictures;
                  this.picturesPanel.loading = false;
              });
          },
          openPanel() {
              this.picturesPanel.visible = true;
              this.load();
          },
      },
      watch: {
          val(newVal) {
              this.$emit('input', newVal);
          }
      },
      data() {
          return {
              picturesPanel: {
                  loading: true,
                  list: [],
                  visible: false,
              },
              deletePanel: {
                  picture: null,
                  loading: false,
                  visible: false,
              },
              val: this.value
          }
      }
  }
</script>
