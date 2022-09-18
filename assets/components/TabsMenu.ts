import {css, html, LitElement} from 'lit';
import {customElement, property, query} from 'lit/decorators.js';


@customElement('tabs-menu')
export class TabsMenu extends LitElement {
    static styles = css``

    @property({type: Array<string>})
    tabs = []

    private redirectTo(tab: string)
    {
        let hrefParts: Array<string> = document.location.href.split('/')
        hrefParts.pop();
        hrefParts.push(tab)

        document.location = hrefParts.join('/')
    }

    private renderTabs() {
        return html`
            ${this.tabs.map(tab => {
                return html`
                    <mwc-button
                            id="prominentButton"
                            outlined
                            label="${tab}"
                            @click="${() => this.redirectTo(tab)}"
                    >
                    </mwc-button>
                `
            })}
        `
    }

    protected render() {
        return html`
            <div class="tabs">
                ${this.renderTabs()}
            </div>
        `
    }
}
