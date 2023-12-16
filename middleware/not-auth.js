export default async function ({ route, store, redirect }) {
  await store.dispatch('nuxtClientInit');
  if (store.state.auth.loggedIn) {
    return redirect('/')
  }
}
