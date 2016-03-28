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
      if (standard[this.state.fields[key].field]) {
	    const cname = this.state.fields[key].field;
        rows.push(<div className={cname} key={key}>
          <h3>{this.state.fields[key].subject}</h3>
          <p dangerouslySetInnerHTML={{ __html: standard[this.state.fields[key].field] }} />
        </div>);
      }
    }

    return (
      <div className="guide-clause-container">
	    <div className="whole-document">
		  <div className="meta-info-wrap">
		    <div className="meta-info">
			  <label>단체협약 목차</label>
			</div>
		  </div>
	      <div className="guide-document document">
            <h2>{standard.subject}</h2>
            <p classNname="guide-content" dangerouslySetInnerHTML={{ __html: standard.content }} />
            {rows}
		  </div>
        </div>
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
