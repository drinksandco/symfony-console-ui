import { writable } from 'svelte/store';
import { ConsoleCommandType } from '../model/ConsoleCommandType';

export interface AppState {
  currentTab: string;
  apiUrl: URL | null;
  socketUrl: URL | null;
  consoleCommands: Array<ConsoleCommandType>;
  tabs: Array<string>;
}

export const store = writable({
  currentTab: 'root',
  apiUrl: new URL('http://localhost:3000'),
  socketUrl: new URL('http://localhost:3001'),
  consoleCommands: [],
  tabs: ['root'],
} as AppState);

export function updateStore(state: AppState) {
  store.update(() => state);
}
