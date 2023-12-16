export const getParentComponent = (component, name) => {
  let neededParent = null;
  let parent = component.$parent;
  while (parent && !neededParent) {
    if (parent.$options.name === name) {
      neededParent = parent;
    }
    parent = parent.$parent
  }
  return neededParent;
}
