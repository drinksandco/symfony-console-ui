import { css, html, LitElement } from 'lit';
import { customElement, property, query } from 'lit/decorators.js';
import { StoreSubscriber } from 'lit-svelte-stores';
import { AppState, store } from '../store/AppState';

@customElement('tabs-menu')
export class TabsMenu extends LitElement {
  static styles = css``;

  @property() store: StoreSubscriber<AppState>;

  constructor() {
    super();
    this.store = new StoreSubscriber(this, () => store);
  }

  private static redirectTo(tab: string) {
    const hrefParts: Array<string> = document.location.href.split('/');
    hrefParts.pop();
    hrefParts.push(tab);

    document.location = hrefParts.join('/');
  }

  private renderTabs() {
    return html`
      ${this.store.value.tabs.map(
        tab => html`
          <mwc-button
            id="prominentButton"
            outlined
            label="${tab}"
            @click="${() => TabsMenu.redirectTo(tab)}"
          >
          </mwc-button>
        `
      )}
    `;
  }

  protected render() {
    return html` <div class="tabs">${this.renderTabs()}</div> `;
  }
}
