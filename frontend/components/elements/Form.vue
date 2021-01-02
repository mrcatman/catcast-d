<template>
<div class="form" @keyup="handleKeyup" ref="form">
	<m-response :data="response" />
	<div class="form__inputs">
		<slot></slot>
	</div>
	<div class="form__submit" >
		<m-btn primary :class="buttonClass" :disabled="disabled" :loading="isLoading" @click="submitForm()">{{buttonText}}</m-btn>
	</div>
</div>
</template>
<style lang="scss">
.form {
	&__submit{
		margin:1em 0 0;
	}
}
</style>
<script>
export default{
	props: {
	  buttonClass: {
	    type: String,
      required: false,
      default: '',
    },
	  disabled: {
      type: [Boolean],
      required: false,
    },
		method: {
			type: String,
			required: false,
			default: 'post',
		},
		buttonText: {
			type: String,
			required: true,
		},
		url: {
			type: String,
			required: false,
		},
		postData :{
			type: [Object, Array],
			required: false,
		},
		check: {
			type: [Function],
			required: false,
		},
	},
	data() {
		return {
			response: {
				status: -1,
				text: '',
			},
			isLoading: false,
		}
	},
	methods: {
		handleKeyup(e) {
			this.response = {
			  status: -1,
        text: ''
			};
			if (e.keyCode === 13) {
			  let activeElement =  document.activeElement;
			  if (!activeElement.classList.contains('ql-editor')) {
          this.submitForm();
        }
			}
		},
		submitForm() {
		  if (!this.disabled) {

        if (this.check) {
          let status = this.check();
          if (!status.status) {
            this.response = status;
            return false;
          }
        }
        this.isLoading = true;
        if (this.url) {
          this.$axios({
            method: this.method,
            url: this.url,
            data: this.postData
          }).then(res => {
            this.isLoading = false;
            this.response = res.data;
            this.$emit('response', res.data);
            if (res.data.status) {
              this.$emit('success', res.data);
            } else {
              this.$emit('fail', res.data);
            }
          })
        }
      }
		}
	}
}
</script>
