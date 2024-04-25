<template>
  <div>
    <c-form :button-text="$t('global.save')" :postData="postData" @response="onResponse" method="put" :url="'/channels/'+channel.id">
      <div class="dashboard__input-container">
        <c-checkbox :title="$t('dashboard.donates.is_on')" v-model="donates" />
      </div>
      <div v-if="donates">
        <!--
        <div class="dashboard-page__section-title">{{$t('dashboard.donates.wallet_settings')}}</div>
        <div class="row row--centered row--wrap row--no-mobile">
          <div class="col">
            <div class="dashboard__input-container">
              <c-input :errors="errors.wallet_type" :title="$t('dashboard.donates.wallet_type')" v-model="additional_settings.donates.wallet_type" />
            </div>
          </div>
          <div class="col">
            <div class="dashboard__input-container">
              <c-input :errors="errors.wallet_id" :title="$t('dashboard.donates.wallet_id')"  v-model="additional_settings.donates.wallet_id" />
            </div>
          </div>
        </div>
        -->

        <div class="row row--centered row--wrap row--no-mobile">
          <div class="col">
            <div class="dashboard__input-container">
              <c-input :errors="errors.minimal_sum" :title="$t('dashboard.donates.minimal_sum')" v-model="additional_settings.donates.minimal_sum" />
            </div>
          </div>
          <div class="col">
            <div class="dashboard__input-container">
              <c-checkbox :errors="errors.accept_from_guests" :title="$t('dashboard.donates.accept_from_guests')" v-model="additional_settings.donates.accept_from_guests" />
            </div>
          </div>
        </div>

        <div class="dashboard-page__section-title">{{$t('dashboard.donates.visibility_settings')}}</div>
        <div class="row row--centered row--wrap row--no-mobile">
          <div class="col">
            <div class="dashboard__input-container">
              <c-checkbox :title="$t('dashboard.donates.show_last_donates')" v-model="additional_settings.donates.show_last_donates" />
            </div>
          </div>
          <div class="col">
            <div class="dashboard__input-container">
              <c-checkbox :title="$t('dashboard.donates.show_in_player')" v-model="additional_settings.donates.show_in_player" />
            </div>
          </div>
          <div class="col" v-show="additional_settings.donates.show_in_player">
            <div class="dashboard__input-container">
              <c-input :errors="errors.minimal_visible_sum" type="number" :title="$t('dashboard.donates.minimal_visible_sum')" v-model="additional_settings.donates.minimal_visible_sum" />
            </div>
          </div>
        </div>
        <div class="row row--centered row--wrap row--no-mobile">
          <div class="col">
            <div class="dashboard__input-container">
              <c-input :errors="errors.donate_comment" :title="$t('dashboard.donates.donate_comment')"  v-model="additional_settings.donates.donate_comment" />
            </div>
          </div>
          <div class="col">
            <div class="dashboard__input-container">
              <c-input :errors="errors.button_text" :title="$t('dashboard.donates.button_text')"  v-model="additional_settings.donates.button_text" />
            </div>
          </div>
        </div>
      </div>
    </c-form>
  </div>
</template>
<script>
  export default {
    computed: {
      postData() {
        return {
          donates: this.donates,
          additional_settings: this.additional_settings
        };
      }
    },
    data() {
      return {
        donates: this.channel.donates,
        additional_settings: {
          donates: this.channel.additional_settings.donates
        },
        errors: {},
      }
    },
    props: {
      channel: {
        type: [Object],
        required: true
      }
    },
    methods: {
      onResponse(res) {
      //  this.$refs.main.scrollTop = 0;
        if (res.errors) {
          Object.keys(res.errors).forEach(key=>{
            let newKey = key.split('.');
            newKey = newKey[newKey.length - 1];
            this.$set(this.errors, newKey, res.errors[key]);
          });
        } else {
          this.errors = {};
        }

      }
    }
  }
</script>
