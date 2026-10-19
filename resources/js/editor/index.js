/*
 * Avian UI - Tiptap bundle for <x-avian::editor>
 *
 * Built into public/js/avian-ui-editor.js with `npm run build:editor`; load it
 * with <x-avian::scripts editor />. An app that bundles its own Tiptap sets
 * window.AvianUITiptap itself, and what it sets wins over this bundle.
 *
 * The add-ons (code.js, emoji.js) import Tiptap and ProseMirror from
 * window.AvianUITiptapModules, so they run on the same instances as this one.
 */
import * as core from '@tiptap/core';
import * as model from '@tiptap/pm/model';
import * as state from '@tiptap/pm/state';
import * as view from '@tiptap/pm/view';
import StarterKit from '@tiptap/starter-kit';
import { Placeholder, CharacterCount } from '@tiptap/extensions';
import Highlight from '@tiptap/extension-highlight';
import Subscript from '@tiptap/extension-subscript';
import Superscript from '@tiptap/extension-superscript';
import { TaskList, TaskItem } from '@tiptap/extension-list';
import Image from '@tiptap/extension-image';
import TextAlign from '@tiptap/extension-text-align';
import { Table, TableRow, TableHeader, TableCell } from '@tiptap/extension-table';
import { TextStyle, Color, FontSize } from '@tiptap/extension-text-style';
import FileHandler from '@tiptap/extension-file-handler';
import { Details, DetailsSummary, DetailsContent } from '@tiptap/extension-details';

window.AvianUITiptapModules = window.AvianUITiptapModules || {
    '@tiptap/core': core,
    '@tiptap/pm/model': model,
    '@tiptap/pm/state': state,
    '@tiptap/pm/view': view,
};

window.AvianUITiptap = Object.assign({
    Editor: core.Editor,
    StarterKit,
    Placeholder,
    CharacterCount,
    Highlight,
    Subscript,
    Superscript,
    TaskList,
    TaskItem,
    Image,
    TextAlign,
    Table,
    TableRow,
    TableHeader,
    TableCell,
    TextStyle,
    Color,
    FontSize,
    FileHandler,
    Details,
    DetailsSummary,
    DetailsContent,
}, window.AvianUITiptap);
