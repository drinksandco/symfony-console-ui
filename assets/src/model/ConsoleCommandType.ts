import { InputArgument } from './InputArgument';
import { InputOption } from './InputOption';

export interface ConsoleCommandType {
  name: string;
  command: string;
  description: string;
  arguments: InputArgument[];
  options: InputOption[];
}
