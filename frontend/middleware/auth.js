export default function ({ store, redirect }) {
  if (!store.state.modules.auth.isLoggedIn) {
    return redirect('/')
  }
}
