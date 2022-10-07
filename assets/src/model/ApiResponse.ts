import { ConsoleCommandType } from './ConsoleCommandType';

export interface ApiResponse {
  id: string;
  commands: Array<ConsoleCommandType>;
  tabs: Array<string>;
}
