import { html, css, LitElement } from 'lit';
import { customElement, property } from 'lit/decorators.js';
import { unsafeHTML } from 'lit/directives/unsafe-html.js';

@customElement('cli-output')
export class CliOutput extends LitElement {
  static styles = css`
    .cli {
      overflow-x: scroll;
      max-width: 90%;
      margin-left: auto;
      margin-right: auto;
      padding: 15px;
      background: #111;
      color: white;
    }
    .info {
      color: green;
    }
    .bg-info {
      background: green;
      padding: 15px;
    }
    .question {
      color: blue;
    }
    .bg-question {
      background: blue;
      padding: 15px;
    }
    .warning {
      color: yellow;
    }
    .bg-warning {
      background: yellow;
      padding: 15px;
    }
    .error {
      color: red;
    }
    .bg-error {
      background: red;
      padding: 15px;
    }
    .command {
      line-height: 65px;
    }
  `;

  @property({ type: String }) content = '';

  private static renderUnClosedTags(
    tag: string,
    line: string,
    className: string
  ) {
    return line
      .split(tag)
      .reduce((previousValue: string, currentValue: string) => {
        if (currentValue.includes('')) {
          return `${previousValue}<span class="${className}">${currentValue.replace(
            '',
            '</span>'
          )}`;
        }

        if (currentValue.includes('<span')) {
          return `${previousValue}<span class="${className}">${currentValue.replace(
            '<span',
            '</span><span'
          )}`;
        }

        return `${previousValue}<span class="${className}">${currentValue}</span>`;
      });
  }

  private static renderUnClosedTagsUsingSpaces(
    tag: string,
    line: string,
    className: string
  ) {
    return line
      .split(tag)
      .reduce((previousValue: string, currentValue: string) => {
        if (currentValue.includes('')) {
          return `${previousValue}<span class="${className}">${currentValue.replace(
            '',
            '</span>'
          )}`;
        }

        if (currentValue.includes('<span')) {
          return `${previousValue}<span class="${className}">${currentValue.replace(
            '<span',
            '</span><span'
          )}`;
        }

        if (currentValue.includes(' ')) {
          return `${previousValue}<span class="${className}">${currentValue.replace(
            ' ',
            '</span> '
          )}`;
        }

        return `${previousValue}<span class="${className}">${currentValue}</span>`;
      });
  }

  private renderContent() {
    const contentLines = this.content.split('\n');
    const content = contentLines.map(line => {
      line = CliOutput.renderUnClosedTags('[39;49m', line, '');
      line = line.replaceAll('[32m', '<code class="info">');
      line = line.replaceAll('[31m', '<code class="error">');
      line = line.replaceAll('[39m', '</code>');
      line = line.replaceAll(
        '[critical]',
        '<code class="error">[critical]</code>'
      );
      line = line.replaceAll(
        '[critical]',
        '<code class="error">[critical]</code>'
      );
      line = line.replaceAll(
        '[debug]',
        '<code class="question">[debug]</code>'
      );
      line = line.replaceAll('[info]', '<code class="info">[info]</code>');
      line = CliOutput.renderUnClosedTags('[33m', line, 'warning');
      line = CliOutput.renderUnClosedTagsUsingSpaces('[36m', line, 'question');
      line = CliOutput.renderUnClosedTags('[33;44m', line, 'question bg-warning');
      line = CliOutput.renderUnClosedTags('[34;43m', line, 'warning bg-question');
      line = CliOutput.renderUnClosedTags('[30;42m', line, 'bg-info');
      line = CliOutput.renderUnClosedTags('[37;41m', line, 'bg-error');
      const urlRegex =
        '(https?:\\/\\/(?:www\\.)?[-a-zA-Z0-9@:%._\\+~#=]{1,256}(:[0-9]+|\\.[a-zA-Z0-9()]{1,6})\\b(?:[-a-zA-Z0-9()@:%_\\+.~#?&\\/=]*))';
      const regex = new RegExp(urlRegex);
      line = line
        .split(' ')
        .map(word => word.replace(regex, '<a href="$1" target="_blank">$1</a>'))
        .join(' ');

      return line;
    });

    content[0] = `<span class="command">${content[0]}</span>`;

    return content.join('\n');
  }

  protected render() {
    return html` <pre class="cli">${unsafeHTML(this.renderContent())}</pre> `;
  }
}
