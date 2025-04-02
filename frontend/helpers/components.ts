import type { ComponentInternalInstance } from 'vue'

export const getParentComponent = (component: ComponentInternalInstance, parentTag: string) => {
  let neededParent = null;
  let parent = component.parent;

  while (parent && !neededParent) {
    console.log(parent);
    // if (parent.name === name) {
    //   neededParent = parent;
    // }
    parent = parent.parent
  }
  return neededParent;
}
