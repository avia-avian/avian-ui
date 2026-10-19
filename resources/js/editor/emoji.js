/*
 * Avian UI - emoji add-on for <x-avian::editor>
 *
 * Load it after the editor bundle with <x-avian::scripts editor="emoji" />.
 * It shares the editor bundle's Tiptap and ProseMirror, so it never runs on
 * its own. GitHub's custom emoji (images on GitHub's CDN) are left out.
 */
import { Emoji, emojis } from '@tiptap/extension-emoji';

window.AvianUITiptap = Object.assign({}, window.AvianUITiptap, {
    Emoji,
    emojis,
});
