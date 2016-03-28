import React, { Component } from 'react';
import axios from 'axios';

export default class Standard extends Component {
  constructor() {
    super();
    this.state = {
      fields: [],
      standard: {},
    };
  }

  render() {
    const standard = this.state.standard;

    let rows = [];
    for (let key in this.state.fields) {
      if (standard[key]) {
        rows.push(<div key={key}>
          <p><b>{this.state.fields[key].subject}</b></p>
          <p dangerouslySetInnerHTML={{ __html: standard[key] }} />
        </div>);
      }
    }

    return (
      <div className="row">
        <p>{standard.subject}</p>
        <p dangerouslySetInnerHTML={{ __html: standard.content }} />
        {rows}
      </div>
    );
  }

  componentWillMount() {
    console.log('- Standard componentWillMount');
    this.doSearch();
  }

  doSearch() {
    const api = '/api/standards';
    const sid = this.props.params.sid;
    const url = `${api}/${sid}`;

    axios.get(url)
    .then(({ data }) => {
      console.log(window.location.pathname, url, data);
      // TODO: checkLogin

      this.setState({
        fields: data.fields.standard,
        standard: data.standard,
      });
    });
  }
}

function getNames(arr) {
  return arr ? arr.reduce((acc, v) => {
    return acc ? `${acc}, ${v.name}` : v.name;
  }, '') : '-';
}
