import React, { Component } from 'react';
import axios from 'axios';

import StandardIndexes from './Standard/StandardIndexes';
import { showSearching, hideSearching } from '../util/utils';

export default class Standard extends Component {
  constructor() {
    super();
    this.state = {
	  path: '',
      fields: [],
	  indexes: [],
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
		    <div className="meta-info guide-indexes">
			  <label>모범단체협약안 목차</label>
              <StandardIndexes
                indexes={this.state.indexes}
                onIndexClick={this.handleIndexClick.bind(this)}
			  />
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
    this.doSearch(true);
  }

  componentDidMount() {
  	this.doUpdated();
  }

  componentWillReceiveProps() {
    this.doSearch(false);
  }

  componentDidUpdate() {
  	this.doUpdated();
  }

  doSearch(init) {
    const api = '/api/standards';
    const sid = window.location.pathname.split("/").splice(-1)[0];
	if(init === true) {
    	var url = `${api}/${sid}?mode=init`;
	} else {
    	var url = `${api}/${sid}`;
	}

    showSearching();
    axios.get(url)
    .then(({ data }) => {
      hideSearching();
      if(init == true) {
        this.setState({
          fields: data.fields.standard,
          indexes: data.indexes,
          standard: data.standard,
        });
      } else {
        this.setState({
          fields: data.fields.standard,
          standard: data.standard,
        });
      }
    });
  }

  doUpdated() {
    const sid = window.location.pathname.split("/").splice(-1)[0];
	this.state.indexes.forEach((index) => {
		if(index.id == sid) {
			window.$('#guide-chapter-'+sid).removeClass('collapsed').siblings().addClass('collapsed');
		} else {
			var find=0;
			index.articles.forEach((article) => {
				if(article.id == sid) {
					window.$('#guide-chapter-'+article.parent).addClass('current').removeClass('collapsed');
					window.$('#guide-article-'+sid).addClass('current');
					find = 1;
				} else {
					window.$('#guide-article-'+article.id).removeClass('current');
				}
			});
			if(!find) {
				window.$('#guide-chapter-'+index.id).addClass('collapsed');
			}
		}
	});
  }

  handleIndexClick(id,nsubs) {
    if(nsubs) {
      window.$('#guide-chapter-'+id).toggleClass('collapsed').siblings().addClass('collapsed');
    }
  }
}

function getNames(arr) {
  return arr ? arr.reduce((acc, v) => {
    return acc ? `${acc}, ${v.name}` : v.name;
  }, '') : '-';
}
