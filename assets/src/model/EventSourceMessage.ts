import { CommandStatus } from './CommandStatus';

export interface EventSourceMessage {
  status: CommandStatus;
  content: string;
}
