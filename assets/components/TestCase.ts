import {html, css, LitElement} from 'lit';
import {customElement, property} from 'lit/decorators.js';
import {TestType} from "../model/TestType";
import {TestStatus} from "../model/TestStatus";
import './TestForm'
import './CliOutput'
import '@material/mwc-button'
import '@material/mwc-circular-progress'
import '@material/mwc-icon'
import '@material/mwc-list'
import {EventSourceMessage} from "../model/EventSourceMessage";

@customElement('test-case')
export class TestCase extends LitElement {
    static styles = css`
        .play-button {
            padding-top: 15
        }
        .test-name p {
            font-weight: bold;
            margin-top: -8;
        }
        .test-status {
            margin-top: -10;
        }
        .side-icons {
            min-width: 120px;
        }
        .side-icons {
            float: left;
            clear: none;
        }
        .side-icons mwc-button {
            margin-left: 5px;
        }
        .side-icons mwc-icon {
            margin-top:5px;
            margin-left: -100px;
        }
        .side-icons mwc-circular-progress {
            margin-left: -100px;
        }
        .success {
            color: green;
        }
        .fail {
            color: red;
        }
        .hidden {
            display: none;
        }
    `

    @property()
    eventSource?: EventSource
    @property()
    testStatus: TestStatus
    @property()
    showCliOutput: boolean
    @property({type: Object as unknown as TestType})
    testType = {name: '', command: '', description: ''}
    @property({type: String})
    cliOutput = ''

    constructor() {
        super();
        this.testStatus = TestStatus.STOPPED
        this.showCliOutput = false
    }

    firstUpdated() {
        this.eventSource = new EventSource(
            'http://localhost:3001/.well-known/mercure?topic=' +
            encodeURIComponent('http://example.com/' + this.testType.name)
        );

        this.eventSource.onmessage = e => {
            const message = JSON.parse(e.data) as EventSourceMessage
            // Will be called every time an update is published by the server
            this.testStatus = TestStatus[message.status]
            if (TestStatus.STOPPED === this.testStatus) {
                this.cliOutput = ''
            }
            this.cliOutput = this.cliOutput + message.content
            this.showCliOutput = true
        }
    }

    private toggleCliOutput() {
        this.showCliOutput = !this.showCliOutput
    }

    protected render() {
        const runningClass: string = this.testStatus === TestStatus.RUNNING ? '' : 'hidden'
        const showSuccessClass: string = [
            TestStatus.STOPPED,
            TestStatus.SUCCEEDED
        ].includes(this.testStatus) ? '': 'hidden'
        const showFailClass: string = [
            TestStatus.FAILED,
        ].includes(this.testStatus) ? '': 'hidden'

        return html`
            <mwc-list-item twoline graphic="medium" hasMeta>
                <span class="test-name">
                    <p>${this.testType.name}</p>
                </span>
                <span slot="graphic" class="material-icons inverted play-button">
                    <test-form testType="${JSON.stringify(this.testType)}"></test-form>
                </span>
                <span slot="meta" class="material-icons side-icons">
                    <mwc-icon class="${showSuccessClass} success">done</mwc-icon>
                    <mwc-icon class="${showFailClass} fail">close</mwc-icon>
                    <mwc-button class="${!runningClass}" outlined label="" icon="description" trailingIcon @click="${this.toggleCliOutput}"></mwc-button>-
                    <mwc-circular-progress indeterminate id="progress" class="${runningClass} test-status" ></mwc-circular-progress>
                </span>
            </mwc-list-item>
            <li class="${this.showCliOutput ? '' : 'hidden'}">
                <cli-output content="${this.cliOutput}"></cli-output>
            </li>
            <li divider role="separator"></li>
        `
    }
}
