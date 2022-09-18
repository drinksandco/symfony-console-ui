import {InputArgument} from "./InputArgument";
import {InputOption} from "./InputOption";

export interface TestType {
    name: string
    command: string
    description: string
    arguments: InputArgument[]
    options: InputOption[]
}
