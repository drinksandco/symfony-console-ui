import { html, css, LitElement } from 'lit';
import { customElement, property } from 'lit/decorators.js';
import { StoreSubscriber } from 'lit-svelte-stores';
import { ConsoleCommandType } from '../model/ConsoleCommandType';
import { CommandStatus } from '../model/CommandStatus';
import './TestForm';
import './CliOutput';
import '@material/mwc-button';
import '@material/mwc-circular-progress';
import '@material/mwc-icon';
import '@material/mwc-list';
import { EventSourceMessage } from '../model/EventSourceMessage';
import { AppState, store } from '../store/AppState';

@customElement('test-case')
export class TestCase extends LitElement {
  static styles = css`
    mwc-list-item {
      cursor: default;
    }
    .play-button {
      padding-top: 15;
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
      margin-top: 5px;
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
  `;

  @property()
  testStatus: CommandStatus;

  @property()
  showCliOutput: boolean;

  @property({ type: Object as unknown as ConsoleCommandType })
  testType = { name: '', command: '', description: '' };

  @property({ type: String })
  cliOutput = '';

  @property()
  store: StoreSubscriber<AppState>;

  constructor() {
    super();
    this.testStatus = CommandStatus.STOPPED;
    this.showCliOutput = false;
    this.store = new StoreSubscriber(this, () => store);
  }

  firstUpdated() {
    const eventSource = new EventSource(
      `${this.store.value.socketUrl}?topic=${encodeURIComponent(
        `http://console.ui/${this.testType.name}`
      )}`
    );

    eventSource.onmessage = e => {
      const message = JSON.parse(e.data) as EventSourceMessage;
      // Will be called every time an update is published by the server
      this.testStatus = CommandStatus[message.status];
      if (CommandStatus.STOPPED === this.testStatus) {
        this.cliOutput = '';
      }
      this.cliOutput += message.content;
      this.showCliOutput = true;
    };
  }

  private toggleCliOutput() {
    this.showCliOutput = !this.showCliOutput;
  }

  protected render() {
    const runningClass: string =
      this.testStatus === CommandStatus.RUNNING ? '' : 'hidden';
    const showSuccessClass: string = [
      CommandStatus.STOPPED,
      CommandStatus.SUCCEEDED,
    ].includes(this.testStatus)
      ? ''
      : 'hidden';
    const showFailClass: string = [CommandStatus.FAILED].includes(
      this.testStatus
    )
      ? ''
      : 'hidden';

    return html`
      <mwc-list-item twoline graphic="medium" hasMeta>
        <span class="test-name">
          <p>${this.testType.name}</p>
        </span>
        <span slot="graphic" class="material-icons inverted play-button">
          <test-form testType="${JSON.stringify(this.testType)}"> </test-form>
        </span>
        <span slot="meta" class="material-icons side-icons">
          <mwc-icon class="${showSuccessClass} success">done</mwc-icon>
          <mwc-icon class="${showFailClass} fail">close</mwc-icon>
          <mwc-button
            class="${!runningClass}"
            outlined
            label=""
            icon="description"
            trailingIcon
            @click="${this.toggleCliOutput}"
          ></mwc-button
          >-
          <mwc-circular-progress
            indeterminate
            id="progress"
            class="${runningClass} test-status"
          ></mwc-circular-progress>
        </span>
      </mwc-list-item>
      <li class="${this.showCliOutput ? '' : 'hidden'}">
        <cli-output content="${this.cliOutput}"></cli-output>
      </li>
      <li divider role="separator"></li>
    `;
  }
}
