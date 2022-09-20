import {html, css, LitElement} from 'lit';
import {customElement, property, query, queryAll} from 'lit/decorators.js';
import {TextField} from '@material/mwc-textfield'
import {Checkbox} from '@material/mwc-checkbox'
import {TestType} from "../model/TestType";
import '@material/mwc-button'
import '@material/mwc-dialog'
import '@material/mwc-icon'
import '@material/mwc-formfield'
import '@material/mwc-textfield'
import '@material/mwc-checkbox'
import {InputArgument} from "../model/InputArgument";
import {api} from "../http-client/HttpClient";
import {InputOption} from "../model/InputOption";
import {unsafeHTML} from 'lit/directives/unsafe-html.js';

@customElement('test-form')
export class TestForm extends LitElement {
    static styles = css`
        mwc-dialog {
            text-align: left;
            --mdc-dialog-min-width: 320px;
        }
        @media (min-width: 560px) { 
            mwc-dialog {
                --mdc-dialog-min-width: 520px;
            }
        }
        @media (min-width: 760px) { 
            mwc-dialog {
                --mdc-dialog-min-width: 720px;
            }
        }
        @media (min-width: 940px) { 
            mwc-dialog {
                --mdc-dialog-min-width: 920px;
            }
        }
        mwc-textfield {
            padding: 20px;
            width: 90%;
        }
        .centered {
            width: 90%;
            margin-left: auto;
            margin-right: auto;
            text-align: center;
        }
        .left-text-pad {
            width: 90%;
            margin-left: auto;
            margin-right: auto;
            text-align: justify;
        }
    `;

    @property({type: Object as unknown as TestType})
    testType = {name: '', command: '', description: '', arguments: [], options: []}
    @property()
    inputArgumentValues: Array<string> = []
    @property()
    inputOptionValues: Array<string> = []
    @property({type: URL})
    consoleEndpoint = 'http://localhost:3000'

    @query('#dialog1')
    dialog!: HTMLDialogElement;

    @queryAll('.textfield-argument mwc-textfield')
    inputArgumentNodes!: NodeList;
    @queryAll('.textfield-option mwc-textfield')
    inputOptionNodes!: NodeList;
    @queryAll('.textfield-option mwc-checkbox')
    inputCheckNodes!: NodeList;

    private openDialog() {
        this.hydrateInputs()
        this.dialog.open = true;
    }

    private hydrateArguments() {
        let inputValues: Array<string> = []

        this.inputArgumentNodes.forEach((value: Node) => {
            let inputElement: TextField
            inputElement = value as TextField
            if ('' !== inputElement.value) {
                inputValues.push(inputElement.value)
            }
        })

        this.inputArgumentValues = inputValues
    }

    private hydrateOptions() {
        let inputValues: Array<string> = []

        this.inputOptionNodes.forEach((value: Node) => {
            let inputElement: TextField
            inputElement = value as TextField
            if ('' !== inputElement.value) {
                inputValues.push(inputElement.label + inputElement.value)
            }
        })

        this.inputOptionValues = inputValues
        this.hydrateOptionChecks()
    }

    private hydrateOptionChecks() {
        let inputValues: Array<string> = []

        this.inputCheckNodes.forEach((value: Node) => {
            let inputElement: Checkbox
            inputElement = value as Checkbox
            const index = this.inputOptionValues.indexOf(inputElement.name)
            if (inputElement.checked && -1 === index) {
                inputValues.push(inputElement.name)
            } else if(!inputElement.checked && index > -1) {
                this.inputOptionValues.splice(index, 1)
            }
        })

        this.inputOptionValues = this.inputOptionValues.concat(inputValues)
    }

    private hydrateInputs() {
        this.hydrateArguments()
        this.hydrateOptions()
    }

    private async submitForm() {
        this.hydrateInputs()

        await api(
            this.consoleEndpoint + '/cli/console-ui/schedule',
            {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    name: this.testType.command,
                    arguments: this.inputArgumentValues,
                    options: this.inputOptionValues,
                })
            }
        );
    }

    private renderArguments() {
        return html`
            <div class="textfield-argument">
                ${this.testType.arguments.map((argument: InputArgument) => {
                    return html`
                        <mwc-textfield
                                outlined
                                label="${argument.name}"
                                helper="${argument.description}"
                                value="${argument.defaultValue}"
                                @keyup="${this.hydrateArguments}"
                        ></mwc-textfield>
                    `
                })}
            </div>
        `
    }

    private renderOptionField(option: InputOption) {
        if (option.acceptValue) {
            return html`
                <mwc-textfield
                        outlined
                        label="--${option.name}="
                        helper="${option.description}"
                        value="${option.defaultValue}"
                        @keyup="${this.hydrateOptions}"
                ></mwc-textfield>
            `
        }

        return html`
            <div class="left-text-pad">
                <mwc-formfield label="--${option.name}">
                    <mwc-checkbox name="--${option.name}" @change="${this.hydrateOptions}""></mwc-checkbox>
                </mwc-formfield>
            </div>
        `
    }

    private renderInputOptions() {
        return html`
            <div class="textfield-option">
                ${this.testType.options.map((option: InputOption) => this.renderOptionField(option))}
            </div>
        `
    }

    private printCommand() {
        return 'bin/console ' + this.testType.name + ' '
            + this.inputArgumentValues.map(value => value + ' ').join().replaceAll(',', '')
            + this.inputOptionValues.map(value => value + ' ').join().replaceAll(',', '')
    }
    render() {
        return html`
            <mwc-button outlined label="" icon="play_arrow" trailingIcon @click="${this.openDialog}"></mwc-button>
            <mwc-dialog id="dialog1" 
                        class=""
                        heading="${this.testType.name}"
            >
                <div>
                    <cli-output
                            content="${this.printCommand()}"
                    ></cli-output>

                    <div class="centered">
                        ${unsafeHTML(
                                this.testType.arguments.length > 0
                                        ? '<div class="left-text-pad"><strong>Input Arguments: </strong></div>'
                                        : ''
                        )}
                        ${this.renderArguments()}

                        ${unsafeHTML(
                                this.testType.options.length > 0
                                        ? '<div class="left-text-pad"><strong>Input Options: </strong></div>'
                                        : ''
                        )}
                        ${this.renderInputOptions()}
                    </div>

                </div>

                <mwc-button slot="primaryAction" dialogAction="ok" icon="play_arrow" @click="${this.submitForm}">Run
                    Command
                </mwc-button>
                <mwc-button slot="secondaryAction" dialogAction="cancel" icon="close">Close</mwc-button>
            </mwc-dialog>
        `;
    }
}
