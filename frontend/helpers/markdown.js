import marked from "marked";

export const parseMarkdown = (text) => {
  return marked.parse(text.trim()); // todo: sanitize
}
