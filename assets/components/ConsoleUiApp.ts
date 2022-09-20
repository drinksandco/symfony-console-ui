import {css, html, LitElement} from 'lit';
import {customElement, property, query} from 'lit/decorators.js';
import {TestType} from "../model/TestType";
import './TestCase'
import './TabsMenu'
import '@material/mwc-list'
import '@material/mwc-top-app-bar-fixed'
import * as stream from "stream";

@customElement('console-ui')
export class ConsoleUiApp extends LitElement {
    static styles = css`
    `

    @property({type: Array<TestType>})
    testsTypes = []

    @property({type: Array<string>})
    tabs = []

    @property({type: URL})
    consoleEndpoint = 'http://localhost:3000'

    renderCommands(testType: TestType) {
        return html`
            <test-case testType=${JSON.stringify(testType)} consoleEndpoint="${this.consoleEndpoint}"></test-case>
        `
    }

    protected render() {
        return html`
            <mwc-top-app-bar-fixed id="bar">
                <div slot="title" id="title">Symfony Console UI</div>

                <tabs-menu tabs="${JSON.stringify(this.tabs)}"></tabs-menu>
                
                <mwc-list class="container">
                    <li divider role="separator"></li>
                    ${this.testsTypes.map((testType: TestType) => this.renderCommands(testType))}
                </mwc-list>

            </mwc-top-app-bar-fixed>
        `
    }
}
