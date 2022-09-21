import {TestStatus} from "./TestStatus";

export interface EventSourceMessage {
    status: TestStatus,
    content: string
}
