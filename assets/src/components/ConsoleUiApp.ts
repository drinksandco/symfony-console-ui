import { html, LitElement } from 'lit';
import { customElement, property } from 'lit/decorators.js';
import { StoreSubscriber } from 'lit-svelte-stores';
import { ConsoleCommandType } from '../model/ConsoleCommandType';
import { AppState, updateStore, store } from '../store/AppState';
import './TestCase';
import './TabsMenu';
import '@material/mwc-list';
import '@material/mwc-top-app-bar-fixed';

@customElement('console-ui')
export class ConsoleUiApp extends LitElement {
  @property({ type: Array<ConsoleCommandType> }) commands = [];

  @property({ type: String }) currentTab = 'root';

  @property({ type: Array<string> }) tabs = ['root'];

  @property({ type: URL }) apiUrl = new URL('http://localhost:3000');

  @property({ type: URL }) socketUrl = new URL('http://localhost:3000');

  @property() store: StoreSubscriber<AppState>;

  constructor() {
    super();
    this.store = new StoreSubscriber(this, () => store);
  }

  protected firstUpdated() {
    updateStore({
      currentTab: this.currentTab,
      apiUrl: this.apiUrl,
      socketUrl: this.socketUrl,
      consoleCommands: this.commands,
      tabs: this.tabs,
    });
  }

  private static renderCommands(consoleCommandType: ConsoleCommandType) {
    return html`
      <test-case testType=${JSON.stringify(consoleCommandType)}></test-case>
    `;
  }

  protected render() {
    return html`
      <mwc-top-app-bar-fixed id="bar">
        <div slot="title" id="title">Symfony Console UI</div>

        <tabs-menu></tabs-menu>

        <mwc-list class="container">
          <li divider role="separator"></li>
          ${this.store.value.consoleCommands.map(
            (testType: ConsoleCommandType) =>
              ConsoleUiApp.renderCommands(testType)
          )}
        </mwc-list>
      </mwc-top-app-bar-fixed>
    `;
  }
}
