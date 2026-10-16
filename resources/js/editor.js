/*
 * Avian UI - Tiptap bundle for <x-avian::editor>
 *
 * Built into public/js/avian-ui-editor.js with `npm run build:editor`; load it
 * with <x-avian::scripts editor />. An app that bundles its own Tiptap sets
 * window.AvianUITiptap itself, and that object wins over this one.
 */
import { Editor } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import { Placeholder } from '@tiptap/extensions';
import Highlight from '@tiptap/extension-highlight';
import { TaskList, TaskItem } from '@tiptap/extension-list';
import Image from '@tiptap/extension-image';
import TextAlign from '@tiptap/extension-text-align';
import { Table, TableRow, TableHeader, TableCell } from '@tiptap/extension-table';
import { TextStyle, Color, FontSize } from '@tiptap/extension-text-style';
import FileHandler from '@tiptap/extension-file-handler';

if (! window.AvianUITiptap) {
    window.AvianUITiptap = {
        Editor,
        StarterKit,
        Placeholder,
        Highlight,
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
    };
}
