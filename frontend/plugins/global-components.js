import Vue from 'vue'
import Button from '@/components/elements/Button.vue'
import Modal from '@/components/elements/Modal.vue'
import Tabs from '@/components/elements/Tabs.vue'
import Input from '@/components/elements/Input.vue'
import CopyTag from '@/components/elements/CopyTag.vue'
import Preloader from '@/components/elements/Preloader.vue'
import PopupMenu from '~/components/elements/popup-menu/PopupMenu'
import PopupMenuHeader from '~/components/elements/popup-menu/PopupMenuHeader'
import PopupMenuItem from '~/components/elements/popup-menu/PopupMenuItem'
import Box from '~/components/elements/Box'
import PictureUploader from '~/components/elements/PictureUploader'
import FormSection from '~/components/elements/FormSection'
import Checkbox from '~/components/elements/Checkbox'
import RadioButtons from '~/components/elements/RadioButtons'
import List from '~/components/elements/list/List'
import ListItem from '~/components/elements/list/ListItem'
import ListItemPicture from '~/components/elements/list/ListItemPicture'
import ListItemTexts from '~/components/elements/list/ListItemTexts'
import ListItemTitle from '~/components/elements/list/ListItemTitle'
import ListItemSub from '~/components/elements/list/ListItemSub'
import ListItemButtons from '~/components/elements/list/ListItemButtons'
import ListBottom from '~/components/elements/list/ListBottom'

import EditableItemsList from '@/components/elements/EditableItemsList'
import Editor from '@/components/elements/Editor'
import Pager from '@/components/elements/Pager'
import Autocomplete from '@/components/elements/Autocomplete'
import Colorpicker from '@/components/elements/Colorpicker'

Vue.component('m-button', Button)
Vue.component('m-modal', Modal)
Vue.component('m-tabs', Tabs)
Vue.component('m-input', Input)
Vue.component('m-editor', Editor)
Vue.component('m-copy-tag', CopyTag)
Vue.component('m-preloader', Preloader)
Vue.component('m-box', Box)
Vue.component('m-picture-uploader', PictureUploader)
Vue.component('m-form-section', FormSection)
Vue.component('m-checkbox', Checkbox)
Vue.component('m-radio-buttons', RadioButtons)
Vue.component('m-popup-menu', PopupMenu)
Vue.component('m-popup-menu-header', PopupMenuHeader)
Vue.component('m-popup-menu-item', PopupMenuItem)
Vue.component('m-editable-items-list', EditableItemsList)
Vue.component('m-pager', Pager)
Vue.component('m-autocomplete', Autocomplete)
Vue.component('m-colorpicker', Colorpicker)

Vue.component('m-list', List)
Vue.component('m-list-item', ListItem)
Vue.component('m-list-item-picture', ListItemPicture)
Vue.component('m-list-item-texts', ListItemTexts)
Vue.component('m-list-item-title', ListItemTitle)
Vue.component('m-list-item-sub', ListItemSub)
Vue.component('m-list-item-buttons', ListItemButtons)
Vue.component('m-list-bottom', ListBottom)
