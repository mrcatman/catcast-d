export default function ({ redirect, $accessor }) {
  if (!$accessor.modules.auth.isAdmin) {
    return redirect('/')
  }
}
